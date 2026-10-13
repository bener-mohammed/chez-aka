<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MenuController extends AbstractController
{
    #[Route('/carte', name: 'app_menu', methods: ['GET'])]
    public function index(
        ProductRepository $productRepository
    ): Response {
        $products = $productRepository->findAvailableForPublicMenu();

        $menuByCategory = [];

        foreach ($products as $product) {
            $category = $product->getCategory();

            if ($category === null) {
                continue;
            }

            $categoryId = $category->getId();

            if (!isset($menuByCategory[$categoryId])) {
                $menuByCategory[$categoryId] = [
                    'category' => $category,
                    'products' => [],
                ];
            }

            $menuByCategory[$categoryId]['products'][] = $product;
        }

        return $this->render('menu/index.html.twig', [
            'menu_by_category' => array_values($menuByCategory),
        ]);
    }
}