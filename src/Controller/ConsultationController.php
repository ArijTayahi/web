<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\SalleAttente;
use App\Entity\Satisfaction;
use App\Entity\StatistiquesSession;
use App\Form\ConsultationType;
use App\Repository\ConsultationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/consultation')]
final class ConsultationController extends AbstractController
{#[IsGranted('ROLE_PATIENT')]
    #[Route('/demander', name: 'app_consultation_demander', methods: ['GET', 'POST'])]
    public function demander(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('demander_consultation', (string) $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_consultation_demander');
            }

            $medecinId = (int) $request->request->get('medecin_id');
            $type      = (string) $request->request->get('type');
            $notes     = trim((string) $request->request->get('notes'));

            $medecin = $userRepository->find($medecinId);
            if (!$medecin) {
                $this->addFlash('error', 'Médecin introuvable.');
                return $this->redirectToRoute('app_consultation_demander');
            }

            /** @var \App\Entity\User $patient */
            $patient = $this->getUser();

            $consultation = new Consultation();
            $consultation->setPatient($patient);
            $consultation->setMedecin($medecin);
            $consultation->setType($type);
            $consultation->setNotes($notes !== '' ? $notes : null);
            $consultation->setStatus('EN_ATTENTE');
            
            // On s'assure que arriveAt est bien setté
            $consultation->setArriveAt(new \DateTime());

            if ($type === 'EN_LIGNE') {
                $consultation->setUrlVsio('https://medismart.tn/visio/' . uniqid('', true));
                $salleAttente = new SalleAttente();
                $salleAttente->setConsultation($consultation);
                $entityManager->persist($salleAttente);
            }

            $entityManager->persist($consultation);
            $entityManager->flush(); // C'est ici que l'erreur se produisait

            $this->addFlash('success', 'Consultation demandée avec succès !');
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        // Récupération des médecins certifiés
        $medecins = $userRepository->findPhysicians();

        return $this->render('consultation/demander.html.twig', [
            'medecins' => $medecins,
        ]);
    }
    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/{id<\d+>}/start', name: 'app_consultation_start', methods: ['POST'])]
    public function start(Consultation $consultation, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('start_consultation_' . $consultation->getId(), (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_doctor_dashboard');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $consultation->setStatus('EN_COURS');
        $consultation->setDateDebut(new \DateTime());

        if ($consultation->getSalleAttente()) {
            $consultation->getSalleAttente()->setStatus('APPELE');
        }

        $entityManager->flush();

        if ($consultation->getType() === 'EN_LIGNE') {
            return $this->redirectToRoute('app_visio_start', ['id' => $consultation->getId()]);
        }

        return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/{id<\d+>}/terminer', name: 'app_consultation_terminer', methods: ['POST'])]
    public function terminer(Consultation $consultation, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('terminer_consultation_' . $consultation->getId(), (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $diagnostic = trim((string) $request->request->get('diagnostic'));
        $notes      = trim((string) $request->request->get('notes'));

        $consultation->setStatus('TERMINEE');
        $consultation->setDateFin(new \DateTime());
        $consultation->setDiagnostic($diagnostic !== '' ? $diagnostic : null);
        $consultation->setNotes($notes !== '' ? $notes : null);

        if ($consultation->getType() === 'EN_LIGNE' && $consultation->getSessionVisio()) {
            $stats = new StatistiquesSession();
            $stats->setConsultation($consultation);

            $debut = $consultation->getDateDebut();
            $fin   = $consultation->getDateFin();

            $stats->setDuree(($debut && $fin) ? (int)(($fin->getTimestamp() - $debut->getTimestamp()) / 60) : 0);
            $stats->setQualiteConnexion('BONNE');
            $stats->setNbMessages($consultation->getMessages()->count());

            $entityManager->persist($stats);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Consultation terminée avec succès.');

        return $this->redirectToRoute('app_ordonnance_create', ['consultationId' => $consultation->getId()]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/{id<\d+>}/evaluer', name: 'app_consultation_evaluer', methods: ['POST'])]
    public function evaluer(Consultation $consultation, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('evaluer_consultation_' . $consultation->getId(), (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getPatient()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($consultation->getStatus() !== 'TERMINEE') {
            $this->addFlash('error', 'Vous ne pouvez évaluer qu\'une consultation terminée.');
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        if ($consultation->getSatisfaction()) {
            $this->addFlash('error', 'Vous avez déjà évalué cette consultation.');
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        $score = (int) $request->request->get('score');
        $commentaire = trim((string) $request->request->get('commentaire'));

        if ($score < 1 || $score > 5) {
            $this->addFlash('error', 'Score invalide.');
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        $satisfaction = new Satisfaction();
        $satisfaction->setConsultation($consultation);
        $satisfaction->setPatient($user);
        $satisfaction->setScore($score);
        $satisfaction->setCommentaire($commentaire !== '' ? $commentaire : null);

        $entityManager->persist($satisfaction);
        $entityManager->flush();

        $this->addFlash('success', 'Merci pour votre évaluation !');

        return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/{id<\d+>}/annuler', name: 'app_consultation_annuler', methods: ['POST'])]
    public function annuler(Consultation $consultation, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('annuler_consultation_' . $consultation->getId(), (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $consultation->setStatus('ANNULEE');
        $entityManager->flush();

        $this->addFlash('success', 'Consultation annulée.');

        return $this->redirectToRoute('app_mes_consultations');
    }

    #[Route('/{id<\d+>}', name: 'app_consultation_show', methods: ['GET'])]
    public function show(Consultation $consultation): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getPatient()->getId() !== $user->getId() && $consultation->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('consultation/show.html.twig', [
            'consultation' => $consultation,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_consultation_edit', methods: ['GET', 'POST'])]
    public function edit(Consultation $consultation, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getPatient()->getId() !== $user->getId() && $consultation->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($consultation->getStatus() !== 'EN_ATTENTE') {
            $this->addFlash('error', 'Vous ne pouvez modifier une consultation que si elle est en attente.');
            return $this->redirectToRoute('app_mes_consultations');
        }

        $form = $this->createForm(ConsultationType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle manual fields for patients
            if ($this->isGranted('ROLE_PATIENT')) {
                $medecinId = (int) $request->request->get('medecin_id');
                $medecin = $userRepository->find($medecinId);
                if ($medecin && $medecin !== $consultation->getMedecin()) {
                    $consultation->setMedecin($medecin);
                }

                $dateDebutStr = $request->request->get('dateDebut');
                if ($dateDebutStr) {
                    $consultation->setDateDebut(new \DateTime($dateDebutStr));
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Consultation modifiée avec succès.');
            return $this->redirectToRoute('app_mes_consultations');
        }

        $medecins = $userRepository->findPhysicians();

        return $this->render('consultation/edit.html.twig', [
            'consultation' => $consultation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}/delete', name: 'app_consultation_delete', methods: ['POST'])]
    public function delete(Consultation $consultation, Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getPatient()->getId() !== $user->getId() && $consultation->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($consultation->getStatus() !== 'EN_ATTENTE') {
            $this->addFlash('error', 'Vous ne pouvez supprimer une consultation que si elle est en attente.');
            return $this->redirectToRoute('app_mes_consultations');
        }

        if (!$this->isCsrfTokenValid('delete_consultation_' . $consultation->getId(), (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        $now = new \DateTimeImmutable();
        $fifteenMinutesAgo = $now->modify('-15 minutes');

        if ($consultation->getCreateAt() > $fifteenMinutesAgo) {
            // Soft delete if within 15 minutes
            $consultation->setDeletedAt($now);
            $entityManager->flush();
            $this->addFlash('success', 'Consultation supprimée avec succès (soft delete).');
        } else {
            // Hard delete if older than 15 minutes
            $entityManager->remove($consultation);
            $entityManager->flush();
            $this->addFlash('success', 'Consultation supprimée définitivement.');
        }

        $this->addFlash('success', 'Consultation supprimée avec succès.');
        return $this->redirectToRoute('app_mes_consultations');
    }

    #[Route('/mes-consultations', name: 'app_mes_consultations', methods: ['GET'])]
    public function mesConsultations(ConsultationRepository $consultationRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_PATIENT')) {
            $consultations = $consultationRepository->findBy(['patient' => $user, 'deletedAt' => null]);
        } elseif ($this->isGranted('ROLE_PHYSICIAN')) {
            $consultations = $consultationRepository->findBy(['medecin' => $user, 'deletedAt' => null]);
        } else {
            $consultations = [];
        }

        return $this->render('consultation/mes_consultations.html.twig', [
            'consultations' => $consultations,
        ]);
    }
}
