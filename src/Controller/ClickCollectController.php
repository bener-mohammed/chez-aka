<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClickCollectController extends AbstractController
{
    #[Route(
        '/click-collect',
        name: 'app_click_collect',
        methods: ['GET']
    )]
    public function index(
        ProductRepository $productRepository
    ): Response {
        $products = $productRepository->findAvailableForClickCollect();

        $productsByCategory = [];

        foreach ($products as $product) {
            $category = $product->getCategory();

            if ($category === null) {
                continue;
            }

            $categoryId = $category->getId();

            if (!isset($productsByCategory[$categoryId])) {
                $productsByCategory[$categoryId] = [
                    'category' => $category,
                    'products' => [],
                ];
            }

            $productsByCategory[$categoryId]['products'][] = $product;
        }

        return $this->render(
            'click_collect/index.html.twig',
            [
                'products_by_category' => array_values(
                    $productsByCategory
                ),
            ]
        );
    }
}