<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Service\ReservationMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        ReservationMailer $reservationMailer
    ): Response {
        $reservation = new Reservation();

        /*
         * Si un utilisateur est connecté, on associe automatiquement
         * la réservation à son compte.
         */
        $user = $this->getUser();

        if ($user instanceof AppUser) {
            $reservation->setUser($user);
        }

        $form = $this->createForm(
            ReservationType::class,
            $reservation
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serviceDay = $reservation->getServiceDay();

            if ($serviceDay === null) {
                $form->get('serviceDay')->addError(
                    new FormError(
                        'Veuillez sélectionner une date de réservation.'
                    )
                );
            } else {
                /*
                 * Sécurité côté serveur :
                 * un jour fermé ne doit jamais accepter de réservation.
                 */
                if (!$serviceDay->isOpen()) {
                    $form->get('serviceDay')->addError(
                        new FormError(
                            'Le restaurant est fermé à cette date.'
                        )
                    );
                }

                /*
                 * On refuse une date passée même si quelqu’un
                 * contourne le formulaire HTML.
                 */
                $today = new \DateTimeImmutable('today');

                if ($serviceDay->getServiceDate() < $today) {
                    $form->get('serviceDay')->addError(
                        new FormError(
                            'Cette date de réservation est déjà passée.'
                        )
                    );
                }

                /*
                 * La limite dépend du jour de service.
                 */
                $partySize = $reservation->getPartySize();

                if (
                    $partySize !== null
                    && $partySize > $serviceDay->getMaxOnlinePartySize()
                ) {
                    $form->get('partySize')->addError(
                        new FormError(
                            sprintf(
                                'Pour cette soirée, les réservations en ligne sont limitées à %d personnes.',
                                $serviceDay->getMaxOnlinePartySize()
                            )
                        )
                    );
                }
            }

            /*
             * Vérification serveur des créneaux.
             * On ne fait pas confiance uniquement au formulaire HTML.
             */
            $reservationTime = $reservation->getReservationTime();

            if ($reservationTime !== null) {
                $allowedTimes = [
                    '20:00',
                    '20:30',
                    '21:00',
                    '21:30',
                ];

                $selectedTime = $reservationTime->format('H:i');

                if (!in_array($selectedTime, $allowedTimes, true)) {
                    $form->get('reservationTime')->addError(
                        new FormError(
                            'Le créneau sélectionné n’est pas disponible.'
                        )
                    );
                }
            }

            /*
             * On vérifie une deuxième fois après avoir ajouté
             * nos erreurs métier.
             */
            if ($form->isValid()) {
                $reservation->setStatus('PENDING');

                /*
                 * La réservation est d'abord enregistrée.
                 * L'e-mail reste une notification secondaire.
                 */
                $entityManager->persist($reservation);
                $entityManager->flush();

                try {
                    $reservationMailer->sendReceived(
                        $reservation
                    );

                    $this->addFlash(
                        'success',
                        'Votre demande de réservation a bien été envoyée. Un e-mail de confirmation de réception vous a été envoyé.'
                    );
                } catch (TransportExceptionInterface) {
                    /*
                     * Même si l'e-mail échoue, la réservation existe
                     * bien en base et reste PENDING.
                     */
                    $this->addFlash(
                        'warning',
                        'Votre demande de réservation a bien été enregistrée, mais l’e-mail de confirmation n’a pas pu être envoyé.'
                    );
                }

                return $this->redirectToRoute(
                    'app_reservation_success'
                );
            }
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(
        '/reservation/confirmation',
        name: 'app_reservation_success',
        methods: ['GET']
    )]
    public function success(): Response
    {
        return $this->render(
            'reservation/success.html.twig'
        );
    }
}