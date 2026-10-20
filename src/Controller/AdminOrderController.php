<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Repository\CustomerOrderRepository;
use App\Service\OrderMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/orders')]
#[IsGranted('ROLE_ADMIN')]
final class AdminOrderController extends AbstractController
{
    #[Route('', name: 'app_admin_order_index', methods: ['GET'])]
    public function index(
        CustomerOrderRepository $customerOrderRepository
    ): Response {
        return $this->render(
            'admin_order/index.html.twig',
            [
                'orders' => $customerOrderRepository->findForAdmin(),
            ]
        );
    }

    #[Route(
        '/{id}',
        name: 'app_admin_order_show',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function show(
        CustomerOrder $customerOrder
    ): Response {
        return $this->render(
            'admin_order/show.html.twig',
            [
                'order' => $customerOrder,
            ]
        );
    }

    #[Route(
        '/{id}/status/{status}',
        name: 'app_admin_order_status',
        requirements: [
            'id' => '\d+',
            'status' => 'PREPARING|READY|COLLECTED',
        ],
        methods: ['POST']
    )]
    public function changeStatus(
        CustomerOrder $customerOrder,
        string $status,
        Request $request,
        EntityManagerInterface $entityManager,
        OrderMailer $orderMailer
    ): Response {
        if (!$this->isCsrfTokenValid(
            'order_status_'.$customerOrder->getId(),
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        $allowedTransitions = [
            'CONFIRMED' => ['PREPARING'],
            'PREPARING' => ['READY'],
            'READY' => ['COLLECTED'],
        ];

        $currentStatus = $customerOrder->getStatus();

        if (
            !isset($allowedTransitions[$currentStatus])
            || !in_array(
                $status,
                $allowedTransitions[$currentStatus],
                true
            )
        ) {
            $this->addFlash(
                'danger',
                'Cette transition de statut n’est pas autorisée.'
            );

            return $this->redirectToRoute(
                'app_admin_order_index'
            );
        }

        $customerOrder
            ->setStatus($status)
            ->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        /*
         * On prévient le client uniquement quand
         * sa commande devient réellement prête.
         */
        if ($status === 'READY') {
            try {
                $orderMailer->sendReady(
                    $customerOrder
                );

                $this->addFlash(
                    'success',
                    'La commande est prête et le client a été averti par e-mail.'
                );
            } catch (TransportExceptionInterface) {
                $this->addFlash(
                    'warning',
                    'La commande est prête, mais l’e-mail n’a pas pu être envoyé.'
                );
            }
        } else {
            $this->addFlash(
                'success',
                'Le statut de la commande a été mis à jour.'
            );
        }

        return $this->redirectToRoute(
            'app_admin_order_index'
        );
    }
}