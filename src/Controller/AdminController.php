<?php

namespace App\Controller;

use App\Entity\Doctor;
use App\Entity\DoctorDocument;
use App\Entity\Patient;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\AvailabilityRepository;
use App\Repository\DoctorDocumentRepository;
use App\Repository\RendezVousRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminController extends AbstractController
{
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/users', name: 'app_admin_users', methods: ['GET'])]
    public function users(Request $request, UserRepository $userRepository): Response
    {
        $search = trim((string) $request->query->get('q'));
        $role = (string) $request->query->get('role');
        $status = (string) $request->query->get('status');

        $qb = $userRepository->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')
            ->leftJoin('u.doctor', 'd')
            ->leftJoin('u.patient', 'p')
            ->addSelect('r', 'd', 'p')
            ->orderBy('u.createdAt', 'DESC');

        if ($search !== '') {
            $qb->andWhere('u.username LIKE :q OR u.email LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }

        if ($role !== '') {
            $roleName = match ($role) {
                'physician' => 'ROLE_PHYSICIAN',
                'patient' => 'ROLE_PATIENT',
                'admin' => 'ROLE_ADMIN',
                'super_admin' => 'ROLE_SUPER_ADMIN',
                default => null,
            };

            if ($roleName) {
                $qb->andWhere('r.name = :roleName')
                    ->setParameter('roleName', $roleName);
            }
        }

        if ($status !== '') {
            if ($status === 'active') {
                $qb->andWhere('u.isActive = 1');
            } elseif ($status === 'inactive') {
                $qb->andWhere('u.isActive = 0');
            } elseif ($status === 'pending_verification') {
                $qb->andWhere('d.id IS NOT NULL')->andWhere('d.isCertified = 0');
            } elseif ($status === 'certified') {
                $qb->andWhere('d.id IS NOT NULL')->andWhere('d.isCertified = 1');
            }
        }

        $users = $qb->getQuery()->getResult();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
        ]);
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/users/create', name: 'app_admin_user_create', methods: ['POST'])]
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        UserPasswordHasherInterface $passwordHasher
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('create_user', $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Invalid form token.');

            return $this->redirectToRoute('app_admin_users');
        }

        $username = trim((string) $request->request->get('username'));
        $email = trim((string) $request->request->get('email'));
        $password = (string) $request->request->get('password');
        $role = (string) $request->request->get('role');

        if ($username === '' || $email === '' || $password === '') {
            $this->addFlash('error', 'Username, email, and password are required.');

            return $this->redirectToRoute('app_admin_users');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Please provide a valid email address.');

            return $this->redirectToRoute('app_admin_users');
        }

        if (strlen($username) < 3) {
            $this->addFlash('error', 'Username must be at least 3 characters.');

            return $this->redirectToRoute('app_admin_users');
        }

        if (strlen($password) < 8) {
            $this->addFlash('error', 'Password must be at least 8 characters.');

            return $this->redirectToRoute('app_admin_users');
        }

        if ($userRepository->findOneBy(['username' => $username])) {
            $this->addFlash('error', 'Username already exists.');

            return $this->redirectToRoute('app_admin_users');
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            $this->addFlash('error', 'Email already exists.');

            return $this->redirectToRoute('app_admin_users');
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $user->setIsActive(true);

        $roleName = match ($role) {
            'physician' => 'ROLE_PHYSICIAN',
            'patient' => 'ROLE_PATIENT',
            'admin' => 'ROLE_ADMIN',
            'super_admin' => 'ROLE_SUPER_ADMIN',
            default => 'ROLE_PATIENT',
        };

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
            $doctor->setIsCertified(false);
            $entityManager->persist($doctor);
        } elseif ($roleName === 'ROLE_PATIENT') {
            $patient = new Patient();
            $patient->setUser($user);
            $entityManager->persist($patient);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'User created successfully.');

        return $this->redirectToRoute('app_admin_users');
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/users/export', name: 'app_admin_users_export', methods: ['GET'])]
    public function exportUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['createdAt' => 'DESC']);

        $lines = [];
        $lines[] = 'id,username,email,roles,active,created_at';

        foreach ($users as $user) {
            $roles = implode('|', $user->getRoles());
            $createdAt = $user->getCreatedAt() ? $user->getCreatedAt()->format('Y-m-d H:i:s') : '';
            $lines[] = sprintf(
                '%d,%s,%s,%s,%s,%s',
                $user->getId(),
                $this->escapeCsv($user->getUsername()),
                $this->escapeCsv($user->getEmail()),
                $this->escapeCsv($roles),
                $user->isActive() ? '1' : '0',
                $createdAt
            );
        }

        $content = implode("\n", $lines);

        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="users_export.csv"',
            ]
        );
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/users/{id}/update', name: 'app_admin_user_update', methods: ['POST'])]
    public function updateUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        RoleRepository $roleRepository
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('update_user_' . $user->getId(), $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Invalid form token.');

            return $this->redirectToRoute('app_admin_users');
        }

        $username = trim((string) $request->request->get('username'));
        $email = trim((string) $request->request->get('email'));
        $role = (string) $request->request->get('role');

        if ($username === '' || $email === '') {
            $this->addFlash('error', 'Username and email are required.');

            return $this->redirectToRoute('app_admin_users');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Please provide a valid email address.');

            return $this->redirectToRoute('app_admin_users');
        }

        if (strlen($username) < 3) {
            $this->addFlash('error', 'Username must be at least 3 characters.');

            return $this->redirectToRoute('app_admin_users');
        }

        $existingUsername = $userRepository->findOneBy(['username' => $username]);
        if ($existingUsername && $existingUsername->getId() !== $user->getId()) {
            $this->addFlash('error', 'Username already exists.');

            return $this->redirectToRoute('app_admin_users');
        }

        $existingEmail = $userRepository->findOneBy(['email' => $email]);
        if ($existingEmail && $existingEmail->getId() !== $user->getId()) {
            $this->addFlash('error', 'Email already exists.');

            return $this->redirectToRoute('app_admin_users');
        }

        $user->setUsername($username);
        $user->setEmail($email);

        $roleName = match ($role) {
            'physician' => 'ROLE_PHYSICIAN',
            'patient' => 'ROLE_PATIENT',
            'admin' => 'ROLE_ADMIN',
            'super_admin' => 'ROLE_SUPER_ADMIN',
            default => 'ROLE_PATIENT',
        };

        foreach ($user->getRoleEntities() as $roleEntity) {
            $user->removeRoleEntity($roleEntity);
        }

        $roleEntity = $roleRepository->findOneBy(['name' => $roleName]);
        if (!$roleEntity) {
            $roleEntity = new Role();
            $roleEntity->setName($roleName);
            $entityManager->persist($roleEntity);
        }
        $user->addRoleEntity($roleEntity);

        if ($roleName === 'ROLE_PHYSICIAN') {
            if (!$user->getDoctor()) {
                $doctor = new Doctor();
                $doctor->setUser($user);
                $doctor->setIsCertified(false);
                $entityManager->persist($doctor);
            }
            if ($user->getPatient()) {
                $entityManager->remove($user->getPatient());
                $user->setPatient(null);
            }
        } elseif ($roleName === 'ROLE_PATIENT') {
            if (!$user->getPatient()) {
                $patient = new Patient();
                $patient->setUser($user);
                $entityManager->persist($patient);
            }
            if ($user->getDoctor()) {
                $entityManager->remove($user->getDoctor());
                $user->setDoctor(null);
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'User updated successfully.');

        return $this->redirectToRoute('app_admin_users');
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/users/{id}/reset-password', name: 'app_admin_user_reset_password', methods: ['POST'])]
    public function resetPassword(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('reset_password_' . $user->getId(), $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Invalid form token.');

            return $this->redirectToRoute('app_admin_users');
        }

        $newPassword = (string) $request->request->get('new_password');
        if (strlen($newPassword) < 8) {
            $this->addFlash('error', 'Password must be at least 8 characters.');

            return $this->redirectToRoute('app_admin_users');
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $entityManager->flush();

        $this->addFlash('success', 'Password updated successfully.');

        return $this->redirectToRoute('app_admin_users');
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/users/{id}/toggle-active', name: 'app_admin_user_toggle_active', methods: ['POST'])]
    public function toggleActive(User $user, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('toggle_active_' . $user->getId(), $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Invalid form token.');

            return $this->redirectToRoute('app_admin_users');
        }

        $user->setIsActive(!$user->isActive());
        $entityManager->flush();

        $this->addFlash('success', 'User status updated successfully.');

        return $this->redirectToRoute('app_admin_users');
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Invalid form token.');

            return $this->redirectToRoute('app_admin_users');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User deleted successfully.');

        return $this->redirectToRoute('app_admin_users');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/appointments', name: 'app_admin_appointments', methods: ['GET'])]
    public function appointments(RendezVousRepository $rendezVousRepository, AvailabilityRepository $availabilityRepository): Response
    {
        return $this->render('admin/appointments.html.twig', [
            'rendez_vouses' => $rendezVousRepository->findAll(),
            'availabilities' => $availabilityRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/settings', name: 'app_admin_settings', methods: ['GET'])]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig');
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/doctor-verifications', name: 'app_admin_doctor_verifications', methods: ['GET'])]
    public function doctorVerifications(DoctorDocumentRepository $repository): Response
    {
        $documents = $repository->findBy(['status' => 'pending'], ['uploadedAt' => 'ASC']);

        return $this->render('admin/doctor_verifications.html.twig', [
            'documents' => $documents,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/doctor-verifications/{id}/approve', name: 'app_admin_doctor_verification_approve', methods: ['POST'])]
    public function approveDocument(
        DoctorDocument $document,
        Request $request,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('approve_document_' . $document->getId(), $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Invalid form token.');

            return $this->redirectToRoute('app_admin_doctor_verifications');
        }

        $document->setStatus('approved');
        $doctor = $document->getDoctor();
        $doctor->setIsCertified(true);
        $doctor->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $this->addFlash('success', 'Doctor verified successfully.');

        return $this->redirectToRoute('app_admin_doctor_verifications');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/doctor-verifications/{id}/reject', name: 'app_admin_doctor_verification_reject', methods: ['POST'])]
    public function rejectDocument(
        DoctorDocument $document,
        Request $request,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('reject_document_' . $document->getId(), $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Invalid form token.');

            return $this->redirectToRoute('app_admin_doctor_verifications');
        }

        $document->setStatus('rejected');
        $doctor = $document->getDoctor();
        $doctor->setIsCertified(false);
        $doctor->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $this->addFlash('success', 'Document rejected.');

        return $this->redirectToRoute('app_admin_doctor_verifications');
    }

    private function escapeCsv(?string $value): string
    {
        $value = (string) $value;
        $value = str_replace('"', '""', $value);

        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . $value . '"';
        }

        return $value;
    }
}
