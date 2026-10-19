<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class StripePaymentService
{
    private StripeClient $stripe;

    public function __construct(
        #[Autowire('%stripe.secret_key%')]
        string $stripeSecretKey
    ) {
        $this->stripe = new StripeClient(
            $stripeSecretKey
        );
    }

    public function createCheckoutSession(
        CustomerOrder $order,
        string $successUrl,
        string $cancelUrl
    ): Session {
        if ($order->getId() === null) {
            throw new \LogicException(
                'La commande doit être enregistrée avant le paiement.'
            );
        }

        if ($order->getOrderNumber() === null) {
            throw new \LogicException(
                'La commande doit avoir un numéro.'
            );
        }

        if ($order->getUser() === null) {
            throw new \LogicException(
                'La commande doit appartenir à un utilisateur.'
            );
        }

        $lineItems = [];
        $computedTotalCents = 0;

        foreach ($order->getOrderItems() as $orderItem) {
            $product = $orderItem->getProduct();
            $quantity = $orderItem->getQuantity();
            $unitPrice = $orderItem->getUnitPrice();

            if (
                $product === null
                || $quantity === null
                || $quantity <= 0
                || $unitPrice === null
            ) {
                continue;
            }

            $unitAmountCents = $this->decimalToCents(
                $unitPrice
            );

            $computedTotalCents +=
                $unitAmountCents * $quantity;

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',

                    'unit_amount' => $unitAmountCents,

                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                ],

                'quantity' => $quantity,
            ];
        }

        if ($lineItems === []) {
            throw new \LogicException(
                'La commande ne contient aucun article payable.'
            );
        }

        $orderTotalCents = $this->decimalToCents(
            $order->getTotalAmount() ?? '0.00'
        );

        if ($computedTotalCents !== $orderTotalCents) {
            throw new \LogicException(
                'Le total de la commande ne correspond pas aux lignes de commande.'
            );
        }

        return $this->stripe
            ->checkout
            ->sessions
            ->create([
                'mode' => 'payment',

                'line_items' => $lineItems,

                'success_url' => $successUrl,

                'cancel_url' => $cancelUrl,

                'client_reference_id' =>
                $order->getOrderNumber(),

                'customer_email' =>
                $order->getUser()->getEmail(),

                'metadata' => [
                    'order_id' =>
                    (string) $order->getId(),

                    'order_number' =>
                    $order->getOrderNumber(),
                ],
            ]);
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
