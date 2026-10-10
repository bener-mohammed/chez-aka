<?php

namespace App\Command;

use App\Entity\AppUser;
use App\Repository\AppUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un compte administrateur Chez Aka',
)]
final class CreateAdminCommand
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private AppUserRepository $appUserRepository,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $io->title('Création d’un administrateur Chez Aka');

        $firstName = trim((string) $io->ask('Prénom'));

        if ($firstName === '') {
            $io->error('Le prénom est obligatoire.');

            return Command::FAILURE;
        }

        $lastName = trim((string) $io->ask('Nom'));

        if ($lastName === '') {
            $io->error('Le nom est obligatoire.');

            return Command::FAILURE;
        }

        $email = strtolower(trim((string) $io->ask('Email')));

        if ($email === '') {
            $io->error('L’adresse email est obligatoire.');

            return Command::FAILURE;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('L’adresse email n’est pas valide.');

            return Command::FAILURE;
        }

        $existingUser = $this->appUserRepository->findOneBy([
            'email' => $email,
        ]);

        if ($existingUser !== null) {
            $io->error('Un utilisateur avec cette adresse email existe déjà.');

            return Command::FAILURE;
        }

        $phoneNumber = trim((string) $io->ask('Téléphone'));

        if ($phoneNumber === '') {
            $io->error('Le numéro de téléphone est obligatoire.');

            return Command::FAILURE;
        }

        $plainPassword = $io->askHidden('Mot de passe');

        if (!$plainPassword || strlen($plainPassword) < 12) {
            $io->error('Le mot de passe doit contenir au moins 12 caractères.');

            return Command::FAILURE;
        }

        $passwordConfirmation = $io->askHidden(
            'Confirmez le mot de passe'
        );

        if ($plainPassword !== $passwordConfirmation) {
            $io->error('Les deux mots de passe ne correspondent pas.');

            return Command::FAILURE;
        }

        $admin = new AppUser();

        $admin
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setPhoneNumber($phoneNumber)
            ->setRole('ROLE_ADMIN')
            ->setIsActive(true);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            $plainPassword
        );

        $admin->setPasswordHash($hashedPassword);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Le compte administrateur %s %s a été créé.',
            $admin->getFirstName(),
            $admin->getLastName()
        ));

        return Command::SUCCESS;
    }
}