<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route(
        '/inscription',
        name: 'app_register',
        methods: ['GET', 'POST']
    )]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_click_collect');
        }

        $user = new AppUser();

        $form = $this->createForm(
            RegistrationFormType::class,
            $user
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form
                ->get('plainPassword')
                ->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainPassword
            );

            $user->setPasswordHash($hashedPassword);
            $user->setRole('ROLE_USER');
            $user->setIsActive(true);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre compte a été créé. Vous pouvez maintenant vous connecter.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render(
            'registration/register.html.twig',
            [
                'registrationForm' => $form,
            ]
        );
    }
}