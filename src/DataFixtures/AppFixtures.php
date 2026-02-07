<?php

namespace App\DataFixtures;

use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Create roles
        $roleUser = new Role();
        $roleUser->setName('ROLE_USER');
        $manager->persist($roleUser);

        $rolePatient = new Role();
        $rolePatient->setName('ROLE_PATIENT');
        $manager->persist($rolePatient);

        $rolePhysician = new Role();
        $rolePhysician->setName('ROLE_PHYSICIAN');
        $manager->persist($rolePhysician);

        $roleAdmin = new Role();
        $roleAdmin->setName('ROLE_ADMIN');
        $manager->persist($roleAdmin);

        $roleSuperAdmin = new Role();
        $roleSuperAdmin->setName('ROLE_SUPER_ADMIN');
        $manager->persist($roleSuperAdmin);

        $manager->flush();

        // Super Admin User
        $superAdmin = new User();
        $superAdmin->setUsername('superadmin');
        $superAdmin->setEmail('superadmin@medilab.local');
        $superAdmin->setPassword($this->passwordHasher->hashPassword($superAdmin, 'SuperAdmin@2026'));
        $superAdmin->setIsActive(true);
        $superAdmin->addRoleEntity($roleSuperAdmin);
        $superAdmin->addRoleEntity($roleAdmin);
        $manager->persist($superAdmin);

        // Admin User
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@medilab.local');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin@2026'));
        $admin->setIsActive(true);
        $admin->addRoleEntity($roleAdmin);
        $manager->persist($admin);

        // Doctor users with profiles
        $doctors = [
            ['username' => 'dr.smith', 'email' => 'dr.smith@medilab.local', 'password' => 'DoctorSmith@2026', 'license' => 'LIC-001-SMITH'],
            ['username' => 'dr.johnson', 'email' => 'dr.johnson@medilab.local', 'password' => 'DoctorJohnson@2026', 'license' => 'LIC-002-JOHNSON'],
            ['username' => 'dr.williams', 'email' => 'dr.williams@medilab.local', 'password' => 'DoctorWilliams@2026', 'license' => 'LIC-003-WILLIAMS'],
            ['username' => 'dr.brown', 'email' => 'dr.brown@medilab.local', 'password' => 'DoctorBrown@2026', 'license' => 'LIC-004-BROWN'],
        ];

        foreach ($doctors as $doctorData) {
            $user = new User();
            $user->setUsername($doctorData['username']);
            $user->setEmail($doctorData['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $doctorData['password']));
            $user->setIsActive(true);
            $user->addRoleEntity($rolePhysician);
            $manager->persist($user);

            $doctor = new Doctor();
            $doctor->setUser($user);
            $doctor->setLicenseCode($doctorData['license']);
            $doctor->setIsCertified(true);
            $manager->persist($doctor);
        }

        // Patient users with profiles
        $patients = [
            ['username' => 'patient1', 'email' => 'patient1@medilab.local', 'password' => 'Patient@2026', 'region' => 'North Region'],
            ['username' => 'patient2', 'email' => 'patient2@medilab.local', 'password' => 'Patient@2026', 'region' => 'South Region'],
            ['username' => 'patient3', 'email' => 'patient3@medilab.local', 'password' => 'Patient@2026', 'region' => 'East Region'],
            ['username' => 'patient4', 'email' => 'patient4@medilab.local', 'password' => 'Patient@2026', 'region' => 'West Region'],
            ['username' => 'patient5', 'email' => 'patient5@medilab.local', 'password' => 'Patient@2026', 'region' => 'Central Region'],
            ['username' => 'patient6', 'email' => 'patient6@medilab.local', 'password' => 'Patient@2026', 'region' => 'North Region'],
            ['username' => 'patient7', 'email' => 'patient7@medilab.local', 'password' => 'Patient@2026', 'region' => 'South Region'],
            ['username' => 'patient8', 'email' => 'patient8@medilab.local', 'password' => 'Patient@2026', 'region' => 'East Region'],
        ];

        foreach ($patients as $patientData) {
            $user = new User();
            $user->setUsername($patientData['username']);
            $user->setEmail($patientData['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $patientData['password']));
            $user->setIsActive(true);
            $user->addRoleEntity($rolePatient);
            $manager->persist($user);

            $patient = new Patient();
            $patient->setUser($user);
            $patient->setRegion($patientData['region']);
            $manager->persist($patient);
        }

        $manager->flush();
    }
}
