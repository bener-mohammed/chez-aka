<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class OrderMailer
{
    public function __construct(
        private MailerInterface $mailer,

        #[Autowire('%env(MAILER_FROM)%')]
        private string $fromEmail
    ) {
    }

    public function sendConfirmed(
        CustomerOrder $customerOrder
    ): void {
        $user = $customerOrder->getUser();

        if ($user === null) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(
                new Address(
                    $this->fromEmail,
                    'Chez Aka'
                )
            )
            ->to($user->getEmail())
            ->subject('Commande confirmée - Chez Aka')
            ->htmlTemplate(
                'emails/order_confirmed.html.twig'
            )
            ->context([
                'order' => $customerOrder,
            ]);

        $this->mailer->send($email);
    }

    public function sendReady(
        CustomerOrder $customerOrder
    ): void {
        $user = $customerOrder->getUser();

        if ($user === null) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(
                new Address(
                    $this->fromEmail,
                    'Chez Aka'
                )
            )
            ->to($user->getEmail())
            ->subject('Votre commande est prête - Chez Aka')
            ->htmlTemplate(
                'emails/order_ready.html.twig'
            )
            ->context([
                'order' => $customerOrder,
            ]);

        $this->mailer->send($email);
    }
}