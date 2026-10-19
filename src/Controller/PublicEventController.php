<?php

namespace App\Controller;

use App\Repository\RestaurantEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PublicEventController extends AbstractController
{
    #[Route(
        '/evenements',
        name: 'app_public_events',
        methods: ['GET']
    )]
    public function index(
        RestaurantEventRepository $restaurantEventRepository
    ): Response {
        return $this->render(
            'public_event/index.html.twig',
            [
                'events' => $restaurantEventRepository
                    ->findPublishedUpcoming(),
            ]
        );
    }
}