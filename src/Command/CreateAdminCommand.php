<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand('app:create-admin', 'Create the admin user')]
class CreateAdminCommand extends Command
{

    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Check if user already exists
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => 'amanihaddad@gmail.com']);
        if ($existingUser) {
            $io->warning('L\'utilisateur amanihaddad@gmail.com existe déjà.');
            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail('amanihaddad@gmail.com');
        $user->setNom('Amani Haddad');
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, '12345678');
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        $io->success('Utilisateur admin créé avec succès : amanihaddad@gmail.com');

        return Command::SUCCESS;
    }
}
