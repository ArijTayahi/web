<?php

namespace App\Controller;

use App\Entity\DoctorDocument;
use App\Repository\DoctorDocumentRepository;
use App\Repository\DoctorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DoctorController extends AbstractController
{
    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/doctor', name: 'app_doctor_redirect')]
    public function redirectToDashboard(): Response
    {
        return $this->redirectToRoute('app_doctor_dashboard');
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/doctor/verification', name: 'app_doctor_verification', methods: ['GET'])]
    public function verification(DoctorRepository $doctorRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $doctor = $doctorRepository->findOneBy(['user' => $user]);

        return $this->render('doctor/verification.html.twig', [
            'doctor' => $doctor,
            'documents' => $doctor ? $doctor->getDocuments() : [],
        ]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/doctor/verification/submit', name: 'app_doctor_verify_submit', methods: ['POST'])]
    public function submitVerification(
        Request $request,
        DoctorRepository $doctorRepository,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('doctor_verification', $csrfToken)) {
            $this->addFlash('error', 'Invalid form token. Please try again.');

            return $this->redirectToRoute('app_doctor_verification');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $doctor = $doctorRepository->findOneBy(['user' => $user]);
        if (!$doctor) {
            $this->addFlash('error', 'Doctor profile not found.');

            return $this->redirectToRoute('app_doctor_verification');
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('certification_pdf');
        $licenseNumber = trim((string) $request->request->get('license_number'));

        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            $this->addFlash('error', 'Please upload a valid PDF file.');

            return $this->redirectToRoute('app_doctor_verification');
        }

        if ($file->getClientMimeType() !== 'application/pdf') {
            $this->addFlash('error', 'Only PDF files are allowed.');

            return $this->redirectToRoute('app_doctor_verification');
        }

        $fileSize = $file->getSize();
        if ($fileSize === null || $fileSize <= 0) {
            $this->addFlash('error', 'Unable to read the uploaded file size. Please try again.');

            return $this->redirectToRoute('app_doctor_verification');
        }

        if ($fileSize > 5 * 1024 * 1024) {
            $this->addFlash('error', 'The PDF file is too large (max 5MB).');

            return $this->redirectToRoute('app_doctor_verification');
        }

        if ($licenseNumber === '' && !$doctor->getLicenseCode()) {
            $this->addFlash('error', 'Please provide your license number.');

            return $this->redirectToRoute('app_doctor_verification');
        }

        $projectDir = $this->getParameter('kernel.project_dir');
        $safeFolder = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user->getUsername());
        $uploadDir = $projectDir . '/public/uploads/doctors/' . $safeFolder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $storedName = uniqid('doc_', true) . '.pdf';
        try {
            $file->move($uploadDir, $storedName);
        } catch (\Throwable $exception) {
            $this->addFlash('error', 'Failed to store the uploaded file. Please try again.');

            return $this->redirectToRoute('app_doctor_verification');
        }

        $document = new DoctorDocument();
        $document->setDoctor($doctor);
        $document->setOriginalName($file->getClientOriginalName());
        $document->setStoredName($storedName);
        $document->setFolderName($safeFolder);
        $document->setMimeType($file->getClientMimeType());
        $document->setSize($fileSize);
        $document->setStatus('pending');

        if ($licenseNumber !== '') {
            $doctor->setLicenseCode($licenseNumber);
        }
        $doctor->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($document);
        $entityManager->flush();

        $this->addFlash('success', 'Documents submitted successfully. Await admin review.');

        return $this->redirectToRoute('app_doctor_verification');
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/doctor/verification/document/{id}/delete', name: 'app_doctor_document_delete', methods: ['POST'])]
    public function deleteDocument(
        int $id,
        Request $request,
        DoctorRepository $doctorRepository,
        DoctorDocumentRepository $documentRepository,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('delete_doctor_document_' . $id, $csrfToken)) {
            $this->addFlash('error', 'Invalid form token. Please try again.');

            return $this->redirectToRoute('app_doctor_verification');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $doctor = $doctorRepository->findOneBy(['user' => $user]);
        if (!$doctor) {
            $this->addFlash('error', 'Doctor profile not found.');

            return $this->redirectToRoute('app_doctor_verification');
        }

        $document = $documentRepository->find($id);
        if (!$document || $document->getDoctor()->getId() !== $doctor->getId()) {
            $this->addFlash('error', 'Document not found.');

            return $this->redirectToRoute('app_doctor_verification');
        }

        $projectDir = $this->getParameter('kernel.project_dir');
        $filePath = $projectDir . '/public/uploads/doctors/' . $document->getFolderName() . '/' . $document->getStoredName();
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        $entityManager->remove($document);
        $entityManager->flush();

        $this->addFlash('success', 'Document removed successfully.');

        return $this->redirectToRoute('app_doctor_verification');
    }
}
