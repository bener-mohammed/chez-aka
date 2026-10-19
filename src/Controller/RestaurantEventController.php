<?php

namespace App\Controller;

use App\Entity\RestaurantEvent;
use App\Form\RestaurantEventType;
use App\Repository\RestaurantEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/events')]
#[IsGranted('ROLE_ADMIN')]
final class RestaurantEventController extends AbstractController
{
    #[Route('', name: 'app_restaurant_event_index', methods: ['GET'])]
    public function index(
        RestaurantEventRepository $restaurantEventRepository
    ): Response {
        return $this->render('restaurant_event/index.html.twig', [
            'restaurant_events' => $restaurantEventRepository->findBy(
                [],
                ['startDatetime' => 'DESC']
            ),
        ]);
    }

    #[Route('/new', name: 'app_restaurant_event_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $restaurantEvent = new RestaurantEvent();

        $form = $this->createForm(
            RestaurantEventType::class,
            $restaurantEvent
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($restaurantEvent);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'L’événement a été créé avec succès.'
            );

            return $this->redirectToRoute(
                'app_restaurant_event_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('restaurant_event/new.html.twig', [
            'restaurant_event' => $restaurantEvent,
            'form' => $form,
        ]);
    }

    #[Route(
        '/{id}',
        name: 'app_restaurant_event_show',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function show(
        RestaurantEvent $restaurantEvent
    ): Response {
        return $this->render('restaurant_event/show.html.twig', [
            'restaurant_event' => $restaurantEvent,
        ]);
    }

    #[Route(
        '/{id}/edit',
        name: 'app_restaurant_event_edit',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    public function edit(
        Request $request,
        RestaurantEvent $restaurantEvent,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(
            RestaurantEventType::class,
            $restaurantEvent
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $restaurantEvent->setUpdatedAt(
                new \DateTimeImmutable()
            );

            $entityManager->flush();

            $this->addFlash(
                'success',
                'L’événement a été modifié avec succès.'
            );

            return $this->redirectToRoute(
                'app_restaurant_event_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('restaurant_event/edit.html.twig', [
            'restaurant_event' => $restaurantEvent,
            'form' => $form,
        ]);
    }

    #[Route(
        '/{id}',
        name: 'app_restaurant_event_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function delete(
        Request $request,
        RestaurantEvent $restaurantEvent,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete'.$restaurantEvent->getId(),
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        $entityManager->remove($restaurantEvent);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'L’événement a été supprimé.'
        );

        return $this->redirectToRoute(
            'app_restaurant_event_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}