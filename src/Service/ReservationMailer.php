<?php

namespace App\Service;

use App\Entity\Reservation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class ReservationMailer
{
    public function __construct(
        private MailerInterface $mailer,

        #[Autowire('%env(MAILER_FROM)%')]
        private string $fromEmail
    ) {
    }

    public function sendReceived(
        Reservation $reservation
    ): void {
        $email = (new TemplatedEmail())
            ->from(
                new Address(
                    $this->fromEmail,
                    'Chez Aka'
                )
            )
            ->to($reservation->getEmail())
            ->subject('Votre demande de réservation - Chez Aka')
            ->htmlTemplate(
                'emails/reservation_received.html.twig'
            )
            ->context([
                'reservation' => $reservation,
            ]);

        $this->mailer->send($email);
    }

    public function sendConfirmed(
        Reservation $reservation
    ): void {
        $email = (new TemplatedEmail())
            ->from(
                new Address(
                    $this->fromEmail,
                    'Chez Aka'
                )
            )
            ->to($reservation->getEmail())
            ->subject('Réservation confirmée - Chez Aka')
            ->htmlTemplate(
                'emails/reservation_confirmed.html.twig'
            )
            ->context([
                'reservation' => $reservation,
            ]);

        $this->mailer->send($email);
    }

    public function sendRefused(
        Reservation $reservation
    ): void {
        $email = (new TemplatedEmail())
            ->from(
                new Address(
                    $this->fromEmail,
                    'Chez Aka'
                )
            )
            ->to($reservation->getEmail())
            ->subject('Réponse à votre demande de réservation - Chez Aka')
            ->htmlTemplate(
                'emails/reservation_refused.html.twig'
            )
            ->context([
                'reservation' => $reservation,
            ]);

        $this->mailer->send($email);
    }
}