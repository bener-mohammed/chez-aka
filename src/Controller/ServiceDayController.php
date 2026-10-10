<?php

namespace App\Controller;

use App\Entity\ServiceDay;
use App\Form\ServiceDayType;
use App\Repository\ServiceDayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/service-days')]
#[IsGranted('ROLE_ADMIN')]
final class ServiceDayController extends AbstractController
{
    #[Route('', name: 'app_service_day_index', methods: ['GET'])]
    public function index(ServiceDayRepository $serviceDayRepository): Response
    {
        return $this->render('service_day/index.html.twig', [
            'service_days' => $serviceDayRepository->findBy(
                [],
                ['serviceDate' => 'ASC']
            ),
        ]);
    }

    #[Route('/new', name: 'app_service_day_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $serviceDay = new ServiceDay();

        $form = $this->createForm(ServiceDayType::class, $serviceDay);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($serviceDay);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Le jour de service a été créé avec succès.'
            );

            return $this->redirectToRoute(
                'app_service_day_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('service_day/new.html.twig', [
            'service_day' => $serviceDay,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_service_day_show', methods: ['GET'])]
    public function show(ServiceDay $serviceDay): Response
    {
        return $this->render('service_day/show.html.twig', [
            'service_day' => $serviceDay,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_service_day_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ServiceDay $serviceDay,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ServiceDayType::class, $serviceDay);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serviceDay->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash(
                'success',
                'Le jour de service a été modifié avec succès.'
            );

            return $this->redirectToRoute(
                'app_service_day_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('service_day/edit.html.twig', [
            'service_day' => $serviceDay,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_service_day_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ServiceDay $serviceDay,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$serviceDay->getId(),
            $request->getPayload()->getString('_token')
        )) {
            $entityManager->remove($serviceDay);
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'app_service_day_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}