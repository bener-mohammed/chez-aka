<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Entity\CustomerOrder;
use App\Entity\Payment;
use App\Service\StripePaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/paiement')]
#[IsGranted('ROLE_USER')]
final class PaymentController extends AbstractController
{
    #[Route(
        '/{id}/stripe',
        name: 'app_payment_stripe',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function pay(
        CustomerOrder $customerOrder,
        Request $request,
        StripePaymentService $stripePaymentService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof AppUser) {
            throw $this->createAccessDeniedException();
        }

        if ($customerOrder->getUser() !== $user) {
            throw $this->createAccessDeniedException(
                'Cette commande ne vous appartient pas.'
            );
        }

        if (!$this->isCsrfTokenValid(
            'payment_' . $customerOrder->getId(),
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        if ($customerOrder->getStatus() !== 'PENDING_PAYMENT') {
            $this->addFlash(
                'danger',
                'Cette commande ne peut plus être payée.'
            );

            return $this->redirectToRoute(
                'app_checkout_confirmation',
                [
                    'id' => $customerOrder->getId(),
                ]
            );
        }

        if (
            $customerOrder->getTotalAmount() === null
            || (float) $customerOrder->getTotalAmount() <= 0
        ) {
            $this->addFlash(
                'danger',
                'Le montant de la commande est invalide.'
            );

            return $this->redirectToRoute(
                'app_checkout_confirmation',
                [
                    'id' => $customerOrder->getId(),
                ]
            );
        }

        $payment = new Payment();

        $payment
            ->setCustomerOrder($customerOrder)
            ->setProvider('STRIPE')
            ->setAmount($customerOrder->getTotalAmount())
            ->setStatus('PENDING')
            ->setPaymentMethod('CARD');

        $entityManager->persist($payment);
        $entityManager->flush();

        $successUrl = $this->generateUrl(
            'app_payment_success',
            [
                'id' => $customerOrder->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $successUrl .= '?session_id={CHECKOUT_SESSION_ID}';

        $cancelUrl = $this->generateUrl(
            'app_payment_cancel',
            [
                'id' => $customerOrder->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        try {
            $checkoutSession = $stripePaymentService
                ->createCheckoutSession(
                    $customerOrder,
                    $successUrl,
                    $cancelUrl
                );

            $payment->setTransactionReference(
                $checkoutSession->id
            );

            $entityManager->flush();

            if ($checkoutSession->url === null) {
                throw new \RuntimeException(
                    'Stripe n’a pas retourné d’URL de paiement.'
                );
            }

            return $this->redirect(
                $checkoutSession->url,
                Response::HTTP_SEE_OTHER
            );
        } catch (ApiErrorException | \RuntimeException $exception) {
            $payment->setStatus('FAILED');

            $entityManager->flush();

            /*
             * TEMPORAIRE POUR LE DÉBOGAGE LOCAL.
             * On remettra un message générique une fois
             * l'intégration Stripe fonctionnelle.
             */
            $this->addFlash(
                'danger',
                'Impossible de démarrer le paiement. Veuillez réessayer.'
            );

            return $this->redirectToRoute(
                'app_checkout_confirmation',
                [
                    'id' => $customerOrder->getId(),
                ]
            );
        }
    }

    #[Route(
        '/{id}/succes',
        name: 'app_payment_success',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function success(
        CustomerOrder $customerOrder
    ): Response {
        if (
            $customerOrder->getUser()
            !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        return $this->render(
            'payment/success.html.twig',
            [
                'order' => $customerOrder,
            ]
        );
    }

    #[Route(
        '/{id}/annule',
        name: 'app_payment_cancel',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function cancel(
        CustomerOrder $customerOrder
    ): Response {
        if (
            $customerOrder->getUser()
            !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        return $this->render(
            'payment/cancel.html.twig',
            [
                'order' => $customerOrder,
            ]
        );
    }
}
