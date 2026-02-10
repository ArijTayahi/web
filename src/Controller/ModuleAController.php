<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ModuleAController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET'])]
    public function profileEdit(): Response
    {
        return $this->render('profile/edit_profile.html.twig');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/update', name: 'app_profile_update', methods: ['POST'])]
    public function profileUpdate(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $oldUsername = $user->getUsername();
        $safeOldUsername = $this->sanitizeUsername((string) $oldUsername);

        $email = $request->request->get('email');
        if ($email) {
            $user->setEmail($email);
        }

        $username = $request->request->get('username');
        if ($username) {
            $user->setUsername($username);
        }

        $safeNewUsername = $this->sanitizeUsername((string) $user->getUsername());
        $projectDir = $this->getParameter('kernel.project_dir');

        if ($safeOldUsername && $safeNewUsername && $safeOldUsername !== $safeNewUsername) {
            $oldDir = $projectDir . '/public/uploads/users/' . $safeOldUsername;
            $newDir = $projectDir . '/public/uploads/users/' . $safeNewUsername;
            if (is_dir($oldDir) && !is_dir($newDir)) {
                @rename($oldDir, $newDir);
            }
        }

        /** @var UploadedFile|null $photo */
        $photo = $request->files->get('profile_photo');
        if ($photo instanceof UploadedFile) {
            $allowedExtensions = ['png', 'jpg', 'jpeg'];
            $extension = strtolower((string) $photo->guessExtension());
            if (!$extension || !in_array($extension, $allowedExtensions, true)) {
                $this->addFlash('error', 'Invalid image type. Use PNG or JPG.');
                return $this->redirectToRoute('app_profile_edit');
            }

            if ($photo->getSize() && $photo->getSize() > 2 * 1024 * 1024) {
                $this->addFlash('error', 'Image too large. Max 2MB.');
                return $this->redirectToRoute('app_profile_edit');
            }

            $userDir = $projectDir . '/public/uploads/users/' . $safeNewUsername;
            if (!is_dir($userDir)) {
                mkdir($userDir, 0775, true);
            }

            $filename = 'pfp.' . $extension;
            try {
                $photo->move($userDir, $filename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Unable to upload profile photo.');
                return $this->redirectToRoute('app_profile_edit');
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Profile updated successfully.');
        return $this->redirectToRoute('app_profile_edit');
    }

    private function sanitizeUsername(string $username): string
    {
        return preg_replace('/[^a-zA-Z0-9_.-]/', '_', $username);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/appointments', name: 'app_patient_appointments')]
    public function patientAppointments(): Response
    {
        return $this->render('patient/appointments.html.twig');
    }
}
