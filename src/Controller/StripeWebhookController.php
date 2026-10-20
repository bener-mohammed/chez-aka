<?php

namespace App\Controller;

use App\Repository\PaymentRepository;
use App\Service\OrderMailer;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class StripeWebhookController extends AbstractController
{
    #[Route(
        '/stripe/webhook',
        name: 'app_stripe_webhook',
        methods: ['POST']
    )]
    public function webhook(
        Request $request,
        PaymentRepository $paymentRepository,
        EntityManagerInterface $entityManager,
        OrderMailer $orderMailer,
        #[Autowire('%stripe.webhook_secret%')]
        string $webhookSecret
    ): Response {
        $payload = $request->getContent();

        $signature = $request->headers->get(
            'Stripe-Signature'
        );

        if ($signature === null) {
            return new Response(
                'Signature Stripe manquante.',
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );
        } catch (\UnexpectedValueException) {
            return new Response(
                'Payload Stripe invalide.',
                Response::HTTP_BAD_REQUEST
            );
        } catch (SignatureVerificationException) {
            return new Response(
                'Signature Stripe invalide.',
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($event->type !== 'checkout.session.completed') {
            return new Response(
                'Événement ignoré.',
                Response::HTTP_OK
            );
        }

        $session = $event->data->object;

        if (
            !isset($session->id)
            || !is_string($session->id)
        ) {
            return new Response(
                'Session Stripe invalide.',
                Response::HTTP_BAD_REQUEST
            );
        }

        $payment = $paymentRepository->findOneBy([
            'transactionReference' => $session->id,
        ]);

        if ($payment === null) {
            return new Response(
                'Paiement introuvable.',
                Response::HTTP_NOT_FOUND
            );
        }

        /*
         * Webhook idempotent :
         * un même paiement Stripe ne doit pas
         * être traité plusieurs fois.
         */
        if ($payment->getStatus() === 'SUCCEEDED') {
            return new Response(
                'Paiement déjà traité.',
                Response::HTTP_OK
            );
        }

        if (
            !isset($session->payment_status)
            || $session->payment_status !== 'paid'
        ) {
            return new Response(
                'Paiement non finalisé.',
                Response::HTTP_OK
            );
        }

        $customerOrder = $payment->getCustomerOrder();

        if ($customerOrder === null) {
            return new Response(
                'Commande introuvable.',
                Response::HTTP_BAD_REQUEST
            );
        }

        /*
         * Vérification supplémentaire du montant reçu
         * depuis Stripe.
         */
        if (
            isset($session->amount_total)
            && is_int($session->amount_total)
        ) {
            $expectedAmount = $this->decimalToCents(
                $payment->getAmount() ?? '0.00'
            );

            if ($session->amount_total !== $expectedAmount) {
                return new Response(
                    'Montant Stripe incohérent.',
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        $payment
            ->setStatus('SUCCEEDED')
            ->setPaidAt(new \DateTimeImmutable());

        $customerOrder
            ->setStatus('CONFIRMED')
            ->setUpdatedAt(new \DateTimeImmutable());

        /*
         * La réussite du paiement est enregistrée
         * avant l'envoi de l'e-mail.
         */
        $entityManager->flush();

        try {
            $orderMailer->sendConfirmed(
                $customerOrder
            );
        } catch (TransportExceptionInterface) {
            /*
             * Une panne du service e-mail ne doit jamais
             * faire considérer le paiement Stripe
             * comme ayant échoué.
             */
        }

        return new Response(
            'Webhook traité.',
            Response::HTTP_OK
        );
    }

    private function decimalToCents(
        string $amount
    ): int {
        $normalized = str_replace(
            ',',
            '.',
            trim($amount)
        );

        [$euros, $cents] = array_pad(
            explode('.', $normalized, 2),
            2,
            '0'
        );

        $cents = substr(
            str_pad($cents, 2, '0'),
            0,
            2
        );

        return ((int) $euros * 100)
            + (int) $cents;
    }
}