<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

final class CartService
{
    private const SESSION_KEY = 'click_collect_cart';

    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository
    ) {
    }

    public function add(int $productId): bool
    {
        $product = $this->productRepository
            ->findOneAvailableForClickCollect($productId);

        if ($product === null) {
            return false;
        }

        $session = $this->requestStack->getSession();
        $cart = $session->get(self::SESSION_KEY, []);

        $cart[$productId] = ($cart[$productId] ?? 0) + 1;

        $session->set(self::SESSION_KEY, $cart);

        return true;
    }

    public function update(
        int $productId,
        int $quantity
    ): bool {
        if ($quantity < 1) {
            return false;
        }

        $product = $this->productRepository
            ->findOneAvailableForClickCollect($productId);

        if ($product === null) {
            return false;
        }

        $session = $this->requestStack->getSession();
        $cart = $session->get(self::SESSION_KEY, []);

        if (!isset($cart[$productId])) {
            return false;
        }

        $cart[$productId] = $quantity;

        $session->set(self::SESSION_KEY, $cart);

        return true;
    }

    public function remove(int $productId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::SESSION_KEY, []);

        unset($cart[$productId]);

        $session->set(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        $this->requestStack
            ->getSession()
            ->remove(self::SESSION_KEY);
    }

    public function getCart(): array
    {
        return $this->requestStack
            ->getSession()
            ->get(self::SESSION_KEY, []);
    }

    public function getDetailedCart(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $this->getCart();

        $items = [];
        $totalCents = 0;
        $validCart = [];

        foreach ($cart as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity = (int) $quantity;

            if ($quantity <= 0) {
                continue;
            }

            $product = $this->productRepository
                ->findOneAvailableForClickCollect($productId);

            if ($product === null) {
                continue;
            }

            $unitPriceCents = $this->priceToCents(
                $product->getPrice()
            );

            $lineTotalCents = $unitPriceCents * $quantity;

            $totalCents += $lineTotalCents;

            $validCart[$productId] = $quantity;

            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'lineTotal' => $this->centsToDecimal(
                    $lineTotalCents
                ),
            ];
        }

        if ($validCart !== $cart) {
            $session->set(
                self::SESSION_KEY,
                $validCart
            );
        }

        return [
            'items' => $items,
            'totalQuantity' => array_sum($validCart),
            'totalAmount' => $this->centsToDecimal(
                $totalCents
            ),
        ];
    }

    public function getTotalQuantity(): int
    {
        return array_sum($this->getCart());
    }

    private function priceToCents(string $price): int
    {
        $normalizedPrice = str_replace(
            ',',
            '.',
            trim($price)
        );

        [$euros, $cents] = array_pad(
            explode('.', $normalizedPrice, 2),
            2,
            '0'
        );

        $cents = substr(
            str_pad($cents, 2, '0'),
            0,
            2
        );

        return ((int) $euros * 100) + (int) $cents;
    }

    private function centsToDecimal(int $cents): string
    {
        return sprintf(
            '%d.%02d',
            intdiv($cents, 100),
            $cents % 100
        );
    }
}