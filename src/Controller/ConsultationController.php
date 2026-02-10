<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\User;
use App\Form\ConsultationType;
use App\Repository\ConsultationRepository;
use App\Repository\DoctorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/consultation')]
final class ConsultationController extends AbstractController
{
    #[IsGranted('ROLE_PATIENT')]
    #[Route('/', name: 'app_consultation_index', methods: ['GET'])]
    public function index(ConsultationRepository $consultationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $patient = $user->getPatient();
        $consultations = $consultationRepository->findBy(['patient' => $patient, 'isDeleted' => false]);

        return $this->render('consultation/index.html.twig', [
            'consultations' => $consultations,
        ]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/doctor', name: 'app_consultation_doctor_index', methods: ['GET'])]
    public function doctorIndex(ConsultationRepository $consultationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $doctor = $user->getDoctor();
        $consultations = $consultationRepository->findBy(['doctor' => $doctor, 'isDeleted' => false]);

        return $this->render('consultation/doctor_index.html.twig', [
            'consultations' => $consultations,
        ]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/new', name: 'app_consultation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $consultation = new Consultation();
        $consultation->setPatient($user->getPatient());
        $consultation->setStatus('en attente');

        $form = $this->createForm(ConsultationType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($consultation);
            $entityManager->flush();

            return $this->redirectToRoute('app_consultation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultation/new.html.twig', [
            'consultation' => $consultation,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/{id}', name: 'app_consultation_show', methods: ['GET'])]
    public function show(Consultation $consultation): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($consultation->getPatient() !== $user->getPatient()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('consultation/show.html.twig', [
            'consultation' => $consultation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_consultation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Consultation $consultation, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $isPatient = $this->isGranted('ROLE_PATIENT');
        $isDoctor = $this->isGranted('ROLE_PHYSICIAN');

        // Check access permissions
        if ($isPatient && $consultation->getPatient() !== $user->getPatient()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres consultations.');
        }
        if ($isDoctor && $consultation->getDoctor() !== $user->getDoctor()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que les consultations qui vous sont assignées.');
        }
        if (!$isPatient && !$isDoctor) {
            throw $this->createAccessDeniedException();
        }

        // Patients can only edit consultations in 'en attente' status
        if ($isPatient && $consultation->getStatus() !== 'en attente') {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que les consultations en attente.');
        }

        $form = $this->createForm(ConsultationType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $consultation->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            // Redirect based on user role
            if ($isPatient) {
                return $this->redirectToRoute('app_consultation_index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_consultation_doctor_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('consultation/edit.html.twig', [
            'consultation' => $consultation,
            'form' => $form,
            'is_patient' => $isPatient,
            'is_doctor' => $isDoctor,
        ]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/{id}/cancel', name: 'app_consultation_cancel', methods: ['POST'])]
    public function cancel(Request $request, Consultation $consultation, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($consultation->getPatient() !== $user->getPatient() || $consultation->getStatus() !== 'en attente') {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('cancel'.$consultation->getId(), $request->request->get('_token'))) {
            $consultation->setIsDeleted(true);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_consultation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/{id}/status', name: 'app_consultation_change_status', methods: ['POST'])]
    public function changeStatus(Request $request, Consultation $consultation, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($consultation->getDoctor() !== $user->getDoctor()) {
            throw $this->createAccessDeniedException();
        }

        $newStatus = $request->request->get('status');
        if (in_array($newStatus, ['confirmed', 'cancelled'])) {
            $consultation->setStatus($newStatus);
            $consultation->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_consultation_doctor_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/{id}/delete', name: 'app_consultation_doctor_delete', methods: ['POST'])]
    public function doctorDelete(Request $request, Consultation $consultation, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($consultation->getDoctor() !== $user->getDoctor()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$consultation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($consultation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_consultation_doctor_index', [], Response::HTTP_SEE_OTHER);
    }
}
