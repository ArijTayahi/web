<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-superadmin',
    description: 'Crée un utilisateur superadmin'
)]
class CreateSuperAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Vérifier si le superadmin existe déjà
        $superAdminUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'superadmin']);
        if ($superAdminUser) {
            $io->warning('Le superadmin existe déjà!');
            return Command::SUCCESS;
        }

        // Créer ou récupérer les rôles
        $roleRepository = $this->entityManager->getRepository(Role::class);
        
        $superAdminRole = $roleRepository->findOneBy(['name' => 'ROLE_SUPER_ADMIN']);
        if (!$superAdminRole) {
            $superAdminRole = new Role();
            $superAdminRole->setName('ROLE_SUPER_ADMIN');
            $this->entityManager->persist($superAdminRole);
        }

        $adminRole = $roleRepository->findOneBy(['name' => 'ROLE_ADMIN']);
        if (!$adminRole) {
            $adminRole = new Role();
            $adminRole->setName('ROLE_ADMIN');
            $this->entityManager->persist($adminRole);
        }

        // Créer le superadmin
        $superAdmin = new User();
        $superAdmin->setUsername('superadmin');
        $superAdmin->setEmail('superadmin@medilab.local');
        $superAdmin->setPassword($this->passwordHasher->hashPassword($superAdmin, 'SuperAdmin@2026'));
        $superAdmin->setIsActive(true);
        $superAdmin->addRoleEntity($superAdminRole);
        $superAdmin->addRoleEntity($adminRole);

        $this->entityManager->persist($superAdmin);
        $this->entityManager->flush();

        $io->success('Superadmin créé avec succès!');
        $io->info('Username: superadmin');
        $io->info('Email: superadmin@medilab.local');
        $io->info('Password: SuperAdmin@2026');

        return Command::SUCCESS;
    }
}
