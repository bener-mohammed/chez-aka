<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Entity\CustomerOrder;
use App\Entity\OrderItem;
use App\Entity\ServiceDay;
use App\Form\CheckoutType;
use App\Repository\CustomerOrderRepository;
use App\Service\CartService;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commande')]
#[IsGranted('ROLE_USER')]
final class CheckoutController extends AbstractController
{
    #[Route(
        '/validation',
        name: 'app_checkout',
        methods: ['GET', 'POST']
    )]
    public function checkout(
        Request $request,
        CartService $cartService,
        CustomerOrderRepository $customerOrderRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof AppUser) {
            throw $this->createAccessDeniedException();
        }

        $cart = $cartService->getDetailedCart();

        if ($cart['items'] === []) {
            $this->addFlash(
                'danger',
                'Votre panier est vide.'
            );

            return $this->redirectToRoute(
                'app_cart_index'
            );
        }

        $customerOrder = new CustomerOrder();

        $customerOrder
            ->setUser($user)
            ->setOrderNumber(
                $this->generateOrderNumber(
                    $customerOrderRepository
                )
            )
            ->setStatus('PENDING_PAYMENT')
            ->setTotalAmount(
                $cart['totalAmount']
            );

        $form = $this->createForm(
            CheckoutType::class,
            $customerOrder
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serviceDay = $customerOrder->getServiceDay();
            $pickupTime = $customerOrder->getPickupTime();

            $today = new \DateTimeImmutable('today');
            $now = new \DateTimeImmutable();

            if (
                $serviceDay === null
                || $serviceDay->getServiceDate() === null
                || !$serviceDay->isOpen()
                || !$serviceDay->isClickCollectEnabled()
                || $serviceDay->getServiceDate() < $today
            ) {
                $form->get('serviceDay')->addError(
                    new FormError(
                        'Ce jour n’est pas disponible pour le Click & Collect.'
                    )
                );
            }

            $allowedPickupTimes = [
                '20:00',
                '20:30',
                '21:00',
                '21:30',
                '22:00',
                '22:30',
            ];

            if (
                $pickupTime === null
                || !in_array(
                    $pickupTime->format('H:i'),
                    $allowedPickupTimes,
                    true
                )
            ) {
                $form->get('pickupTime')->addError(
                    new FormError(
                        'L’heure de retrait n’est pas valide.'
                    )
                );
            }

            if (
                $serviceDay !== null
                && $serviceDay->getServiceDate() !== null
                && $pickupTime !== null
                && $serviceDay->getServiceDate()->format('Y-m-d')
                === $now->format('Y-m-d')
            ) {
                $pickupDateTime = new \DateTimeImmutable(
                    $serviceDay
                        ->getServiceDate()
                        ->format('Y-m-d')
                        . ' '
                        . $pickupTime->format('H:i')
                );

                if ($pickupDateTime <= $now) {
                    $form->get('pickupTime')->addError(
                        new FormError(
                            'Cette heure de retrait est déjà passée.'
                        )
                    );
                }
            }

            if ($form->isValid()) {
                $connection = $entityManager->getConnection();

                $connection->beginTransaction();

                try {
                    $lockedServiceDay = $entityManager->find(
                        ServiceDay::class,
                        $serviceDay->getId(),
                        LockMode::PESSIMISTIC_WRITE
                    );

                    if (
                        !$lockedServiceDay instanceof ServiceDay
                        || !$lockedServiceDay->isOpen()
                        || !$lockedServiceDay->isClickCollectEnabled()
                        || $lockedServiceDay->getServiceDate() < $today
                    ) {
                        $connection->rollBack();

                        $this->addFlash(
                            'danger',
                            'Ce jour n’est plus disponible pour le Click & Collect.'
                        );

                        return $this->redirectToRoute(
                            'app_checkout'
                        );
                    }

                    $orderCount = $customerOrderRepository
                        ->countForServiceDay(
                            $lockedServiceDay
                        );

                    $orderLimit = $lockedServiceDay
                        ->getClickCollectOrderLimit();

                    if (
                        $orderLimit !== null
                        && $orderCount >= $orderLimit
                    ) {
                        $connection->rollBack();

                        $this->addFlash(
                            'danger',
                            'La limite de commandes Click & Collect est atteinte pour cette soirée.'
                        );

                        return $this->redirectToRoute(
                            'app_checkout'
                        );
                    }

                    $customerOrder
                        ->setServiceDay($lockedServiceDay)
                        ->setTotalAmount(
                            $cart['totalAmount']
                        );

                    $entityManager->persist(
                        $customerOrder
                    );

                    foreach ($cart['items'] as $item) {
                        $product = $item['product'];
                        $quantity = (int) $item['quantity'];

                        $orderItem = new OrderItem();

                        $orderItem
                            ->setCustomerOrder(
                                $customerOrder
                            )
                            ->setProduct($product)
                            ->setQuantity($quantity)
                            ->setUnitPrice(
                                $product->getPrice()
                            );

                        $entityManager->persist(
                            $orderItem
                        );
                    }

                    $entityManager->flush();

                    $connection->commit();
                } catch (\Throwable $exception) {
                    if (
                        $connection->isTransactionActive()
                    ) {
                        $connection->rollBack();
                    }

                    throw $exception;
                }

                $cartService->clear();

                return $this->redirectToRoute(
                    'app_checkout_confirmation',
                    [
                        'id' => $customerOrder->getId(),
                    ]
                );
            }
        }

        return $this->render(
            'checkout/index.html.twig',
            [
                'checkoutForm' => $form,
                'cart' => $cart,
            ]
        );
    }

    #[Route(
        '/confirmation/{id}',
        name: 'app_checkout_confirmation',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function confirmation(
        CustomerOrder $customerOrder
    ): Response {
        if (
            $customerOrder->getUser()
            !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        return $this->render(
            'checkout/confirmation.html.twig',
            [
                'order' => $customerOrder,
            ]
        );
    }

    private function generateOrderNumber(
        CustomerOrderRepository $repository
    ): string {
        do {
            $orderNumber = sprintf(
                'AKA-%s-%s',
                (new \DateTimeImmutable())->format('Ymd'),
                strtoupper(
                    bin2hex(random_bytes(4))
                )
            );
        } while (
            $repository->findOneBy([
                'orderNumber' => $orderNumber,
            ]) !== null
        );

        return $orderNumber;
    }
}
