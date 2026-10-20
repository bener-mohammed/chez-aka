<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Service\ReservationMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reservations')]
#[IsGranted('ROLE_ADMIN')]
final class AdminReservationController extends AbstractController
{
    #[Route('', name: 'app_admin_reservation_index', methods: ['GET'])]
    public function index(
        ReservationRepository $reservationRepository
    ): Response {
        return $this->render('admin_reservation/index.html.twig', [
            'reservations' => $reservationRepository->findBy(
                [],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    #[Route(
        '/{id}/confirm',
        name: 'app_admin_reservation_confirm',
        methods: ['POST']
    )]
    public function confirm(
        Reservation $reservation,
        Request $request,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $entityManager,
        ReservationMailer $reservationMailer
    ): Response {
        if (!$this->isCsrfTokenValid(
            'confirm'.$reservation->getId(),
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        if ($reservation->getStatus() !== 'PENDING') {
            $this->addFlash(
                'danger',
                'Cette réservation a déjà été traitée.'
            );

            return $this->redirectToRoute(
                'app_admin_reservation_index'
            );
        }

        $confirmedArea = $request
            ->getPayload()
            ->getString('confirmed_area');

        if (!in_array(
            $confirmedArea,
            ['INDOOR', 'TERRACE'],
            true
        )) {
            $this->addFlash(
                'danger',
                'Veuillez sélectionner une zone valide.'
            );

            return $this->redirectToRoute(
                'app_admin_reservation_index'
            );
        }

        $serviceDay = $reservation->getServiceDay();

        if ($serviceDay === null) {
            $this->addFlash(
                'danger',
                'Aucun jour de service associé à cette réservation.'
            );

            return $this->redirectToRoute(
                'app_admin_reservation_index'
            );
        }

        $alreadyConfirmed =
            $reservationRepository->countConfirmedPeopleByArea(
                $serviceDay,
                $confirmedArea
            );

        $capacity = $confirmedArea === 'INDOOR'
            ? $serviceDay->getIndoorCapacity()
            : $serviceDay->getTerraceCapacity();

        $partySize = $reservation->getPartySize() ?? 0;

        if ($alreadyConfirmed + $partySize > $capacity) {
            $areaLabel = $confirmedArea === 'INDOOR'
                ? 'intérieur'
                : 'terrasse';

            $this->addFlash(
                'danger',
                sprintf(
                    'Impossible de confirmer en %s : %d places sont déjà réservées sur %d.',
                    $areaLabel,
                    $alreadyConfirmed,
                    $capacity
                )
            );

            return $this->redirectToRoute(
                'app_admin_reservation_index'
            );
        }

        $reservation
            ->setConfirmedArea($confirmedArea)
            ->setStatus('CONFIRMED')
            ->setProcessedAt(new \DateTimeImmutable());

        /*
         * On sauvegarde d'abord la décision métier.
         * L'e-mail n'est qu'une notification.
         */
        $entityManager->flush();

        try {
            $reservationMailer->sendConfirmed(
                $reservation
            );

            $this->addFlash(
                'success',
                'La réservation a été confirmée et le client a été averti par e-mail.'
            );
        } catch (TransportExceptionInterface) {
            $this->addFlash(
                'warning',
                'La réservation a été confirmée, mais l’e-mail n’a pas pu être envoyé.'
            );
        }

        return $this->redirectToRoute(
            'app_admin_reservation_index'
        );
    }

    #[Route(
        '/{id}/refuse',
        name: 'app_admin_reservation_refuse',
        methods: ['POST']
    )]
    public function refuse(
        Reservation $reservation,
        Request $request,
        EntityManagerInterface $entityManager,
        ReservationMailer $reservationMailer
    ): Response {
        if (!$this->isCsrfTokenValid(
            'refuse'.$reservation->getId(),
            $request->getPayload()->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        if ($reservation->getStatus() !== 'PENDING') {
            $this->addFlash(
                'danger',
                'Cette réservation a déjà été traitée.'
            );

            return $this->redirectToRoute(
                'app_admin_reservation_index'
            );
        }

        $reservation
            ->setStatus('REFUSED')
            ->setConfirmedArea(null)
            ->setProcessedAt(new \DateTimeImmutable());

        $entityManager->flush();

        try {
            $reservationMailer->sendRefused(
                $reservation
            );

            $this->addFlash(
                'success',
                'La réservation a été refusée et le client a été averti par e-mail.'
            );
        } catch (TransportExceptionInterface) {
            $this->addFlash(
                'warning',
                'La réservation a été refusée, mais l’e-mail n’a pas pu être envoyé.'
            );
        }

        return $this->redirectToRoute(
            'app_admin_reservation_index'
        );
    }
}