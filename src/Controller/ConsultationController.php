<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\Ordonnance;
use App\Entity\SalleAttente;
use App\Entity\Satisfaction;
use App\Entity\StatistiquesSession;
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
{
    #[IsGranted('ROLE_PATIENT')]
    #[Route('/demander', name: 'app_consultation_demander', methods: ['GET', 'POST'])]
    public function demander(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('demander_consultation', $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_consultation_demander');
            }

            $medecinId = (int) $request->request->get('medecin_id');
            $type = (string) $request->request->get('type');
            $notes = trim((string) $request->request->get('notes'));

            $medecin = $userRepository->find($medecinId);
            if (!$medecin || !$medecin->getDoctor() || !$medecin->getDoctor()->isCertified()) {
                $this->addFlash('error', 'Médecin non valide ou non certifié.');
                return $this->redirectToRoute('app_consultation_demander');
            }

            /** @var \App\Entity\User $patient */
            $patient = $this->getUser();

            $consultation = new Consultation();
            $consultation->setPatient($patient);
            $consultation->setMedecin($medecin);
            $consultation->setType($type);
            $consultation->setNotes($notes);
            $consultation->setStatus('EN_ATTENTE');

            if ($type === 'EN_LIGNE') {
                $consultation->setUrlVisio('https://medismart.tn/visio/' . uniqid());
                
                $salleAttente = new SalleAttente();
                $salleAttente->setConsultation($consultation);
                $entityManager->persist($salleAttente);
            }

            $entityManager->persist($consultation);
            $entityManager->flush();

            $this->addFlash('success', 'Consultation demandée avec succès.');

            if ($type === 'EN_LIGNE') {
                return $this->redirectToRoute('app_consultation_waiting', ['id' => $consultation->getId()]);
            }

            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        $medecins = $userRepository->createQueryBuilder('u')
            ->leftJoin('u.doctor', 'd')
            ->where('d.isCertified = 1')
            ->getQuery()
            ->getResult();

        return $this->render('consultation/demander.html.twig', [
            'medecins' => $medecins,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'app_consultation_show', methods: ['GET'])]
    public function show(Consultation $consultation): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getPatient()->getId() !== $user->getId() 
            && $consultation->getMedecin()->getId() !== $user->getId()
            && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('consultation/show.html.twig', [
            'consultation' => $consultation,
        ]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/{id}/waiting', name: 'app_consultation_waiting', methods: ['GET'])]
    public function waiting(Consultation $consultation): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getPatient()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($consultation->getStatus() !== 'EN_ATTENTE') {
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        return $this->render('consultation/waiting.html.twig', [
            'consultation' => $consultation,
        ]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/{id}/start', name: 'app_consultation_start', methods: ['POST'])]
    public function start(
        Consultation $consultation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('start_consultation_' . $consultation->getId(), $request->request->get('_csrf_token'))) {
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
    #[Route('/{id}/terminer', name: 'app_consultation_terminer', methods: ['POST'])]
    public function terminer(
        Consultation $consultation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('terminer_consultation_' . $consultation->getId(), $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $diagnostic = trim((string) $request->request->get('diagnostic'));
        $notes = trim((string) $request->request->get('notes'));

        $consultation->setStatus('TERMINEE');
        $consultation->setDateFin(new \DateTime());
        $consultation->setDiagnostic($diagnostic);
        $consultation->setNotes($notes);

        // Créer les statistiques si c'est une consultation en ligne
        if ($consultation->getType() === 'EN_LIGNE' && $consultation->getSessionVisio()) {
            $stats = new StatistiquesSession();
            $stats->setConsultation($consultation);
            
            $debut = $consultation->getDateDebut();
            $fin = $consultation->getDateFin();
            $duree = ($fin->getTimestamp() - $debut->getTimestamp()) / 60; // en minutes
            
            $stats->setDuree((int) $duree);
            $stats->setQualiteConnexion('BONNE');
            $stats->setNbMessages($consultation->getMessages()->count());
            
            $entityManager->persist($stats);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Consultation terminée avec succès.');

        return $this->redirectToRoute('app_ordonnance_create', ['consultationId' => $consultation->getId()]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/{id}/evaluer', name: 'app_consultation_evaluer', methods: ['POST'])]
    public function evaluer(
        Consultation $consultation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('evaluer_consultation_' . $consultation->getId(), $request->request->get('_csrf_token'))) {
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

    #[IsGranted('ROLE_USER')]
    #[Route('/mes-consultations', name: 'app_mes_consultations', methods: ['GET'])]
    public function mesConsultations(ConsultationRepository $repository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $qb = $repository->createQueryBuilder('c');

        if (in_array('ROLE_PHYSICIAN', $user->getRoles())) {
            $qb->where('c.medecin = :user');
        } else {
            $qb->where('c.patient = :user');
        }

        $consultations = $qb
            ->setParameter('user', $user)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('consultation/mes_consultations.html.twig', [
            'consultations' => $consultations,
        ]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/{id}/annuler', name: 'app_consultation_annuler', methods: ['POST'])]
    public function annuler(
        Consultation $consultation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('annuler_consultation_' . $consultation->getId(), $request->request->get('_csrf_token'))) {
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
}