<?php

namespace App\Controller;

use App\Entity\Doctor;
use App\Entity\Patient;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        RoleRepository $roleRepository
    ): Response {
        if ($request->isMethod('GET')) {
            return $this->render('registration/register.html.twig');
        }

        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('registration', $csrfToken)) {
            $this->addFlash('error', 'Invalid form token. Please try again.');

            return $this->redirectToRoute('app_register');
        }

        $username = trim((string) $request->request->get('username'));
        $email = trim((string) $request->request->get('email'));
        $plainPassword = (string) $request->request->get('password');
        $roleType = (string) $request->request->get('role');
        $region = trim((string) $request->request->get('region'));

        if ($username === '' || $email === '' || $plainPassword === '') {
            $this->addFlash('error', 'Please fill in all required fields.');

            return $this->redirectToRoute('app_register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Please provide a valid email address.');

            return $this->redirectToRoute('app_register');
        }

        if (strlen($username) < 3) {
            $this->addFlash('error', 'Username must be at least 3 characters.');

            return $this->redirectToRoute('app_register');
        }

        if (!in_array($roleType, ['patient', 'physician'], true)) {
            $this->addFlash('error', 'Invalid role selection.');

            return $this->redirectToRoute('app_register');
        }

        if ($userRepository->findOneBy(['username' => $username])) {
            $this->addFlash('error', 'Username already exists.');

            return $this->redirectToRoute('app_register');
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            $this->addFlash('error', 'Email already exists.');

            return $this->redirectToRoute('app_register');
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

        $roleName = $roleType === 'physician' ? 'ROLE_PHYSICIAN' : 'ROLE_PATIENT';
        $roleEntity = $roleRepository->findOneBy(['name' => $roleName]);
        if (!$roleEntity) {
            $roleEntity = new Role();
            $roleEntity->setName($roleName);
            $entityManager->persist($roleEntity);
        }

        $user->addRoleEntity($roleEntity);

        if ($roleName === 'ROLE_PHYSICIAN') {
            $doctor = new Doctor();
            $doctor->setUser($user);
            $user->setDoctor($doctor);
            $entityManager->persist($doctor);
        } else {
            $patient = new Patient();
            $patient->setUser($user);
            $patient->setRegion($region !== '' ? $region : null);
            $user->setPatient($patient);
            $entityManager->persist($patient);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Account created successfully. You can now sign in.');

        return $this->redirectToRoute('app_login');
    }
}
