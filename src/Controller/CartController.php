<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
final class CartController extends AbstractController
{
    #[Route('', name: 'app_cart_index', methods: ['GET'])]
    public function index(
        CartService $cartService
    ): Response {
        return $this->render('cart/index.html.twig', [
            'cart' => $cartService->getDetailedCart(),
        ]);
    }

    #[Route(
        '/ajouter/{id}',
        name: 'app_cart_add',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function add(
        int $id,
        Request $request,
        CartService $cartService
    ): Response {
        if (!$this->isCsrfTokenValid(
            'cart_add_'.$id,
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        if (!$cartService->add($id)) {
            $this->addFlash(
                'danger',
                'Ce produit n’est pas disponible en Click & Collect.'
            );

            return $this->redirectToRoute(
                'app_click_collect'
            );
        }

        $this->addFlash(
            'success',
            'Le produit a été ajouté au panier.'
        );

        return $this->redirectToRoute(
            'app_click_collect'
        );
    }

    #[Route(
        '/modifier/{id}',
        name: 'app_cart_update',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function update(
        int $id,
        Request $request,
        CartService $cartService
    ): Response {
        if (!$this->isCsrfTokenValid(
            'cart_update_'.$id,
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        $quantity = $request
            ->getPayload()
            ->getInt('quantity');

        if (!$cartService->update($id, $quantity)) {
            $this->addFlash(
                'danger',
                'Impossible de modifier cet article.'
            );

            return $this->redirectToRoute(
                'app_cart_index'
            );
        }

        $this->addFlash(
            'success',
            'La quantité a été mise à jour.'
        );

        return $this->redirectToRoute(
            'app_cart_index'
        );
    }

    #[Route(
        '/supprimer/{id}',
        name: 'app_cart_remove',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function remove(
        int $id,
        Request $request,
        CartService $cartService
    ): Response {
        if (!$this->isCsrfTokenValid(
            'cart_remove_'.$id,
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        $cartService->remove($id);

        $this->addFlash(
            'success',
            'Le produit a été retiré du panier.'
        );

        return $this->redirectToRoute(
            'app_cart_index'
        );
    }

    #[Route(
        '/vider',
        name: 'app_cart_clear',
        methods: ['POST']
    )]
    public function clear(
        Request $request,
        CartService $cartService
    ): Response {
        if (!$this->isCsrfTokenValid(
            'cart_clear',
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        $cartService->clear();

        $this->addFlash(
            'success',
            'Le panier a été vidé.'
        );

        return $this->redirectToRoute(
            'app_cart_index'
        );
    }
}