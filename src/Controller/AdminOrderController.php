<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Repository\CustomerOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        EntityManagerInterface $entityManager
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

        $this->addFlash(
            'success',
            'Le statut de la commande a été mis à jour.'
        );

        return $this->redirectToRoute(
            'app_admin_order_index'
        );
    }
}