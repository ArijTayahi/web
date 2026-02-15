<?php

namespace App\Controller;

use App\Entity\ConsultationTypeEnum;
use App\Entity\DayOfWeekEnum;
use App\Entity\RendezVous;
use App\Entity\Satisfaction;
use App\Entity\StatusRDVEnum;
use App\Entity\User;
use App\Repository\AvailabilityRepository;
use App\Repository\ConsultationRepository;
use App\Repository\DoctorRepository;
use App\Repository\FactureRepository;
use App\Repository\OrdonnanceRepository;
use App\Repository\RendezVousRepository;
use App\Repository\SatisfactionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PatientController extends AbstractController
{
    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/find-doctor', name: 'app_find_doctor', methods: ['GET'])]
    public function findDoctor(Request $request, DoctorRepository $doctorRepository): Response
    {
        $search = trim((string) $request->query->get('q'));

        $qb = $doctorRepository->createQueryBuilder('d')
            ->leftJoin('d.user', 'u')
            ->addSelect('u')
            ->where('d.isCertified = 1')
            ->orderBy('u.username', 'ASC');

        if ($search !== '') {
            $qb->andWhere('u.username LIKE :q OR u.email LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }

        $doctors = $qb->getQuery()->getResult();

        return $this->render('patient/find_doctor.html.twig', [
            'doctors' => $doctors,
            'search' => $search,
        ]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/profil', name: 'app_patient_profile', methods: ['GET'])]
    public function profile(): Response
    {
        return $this->render('patient/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/ordonnances', name: 'app_patient_ordonnances', methods: ['GET'])]
    public function ordonnances(OrdonnanceRepository $ordonnanceRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        // Assuming Ordonnance is linked to Consultation which is linked to Patient
        // This query joins Ordonnance -> Consultation -> Patient
        $ordonnances = $ordonnanceRepository->createQueryBuilder('o')
            ->leftJoin('o.consultation', 'c')
            ->where('c.patient = :patient')
            ->setParameter('patient', $user->getPatient())
            ->orderBy('c.consultationDate', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('patient/ordonnances.html.twig', [
            'ordonnances' => $ordonnances,
        ]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/consultations', name: 'app_patient_consultations', methods: ['GET'])]
    public function consultations(ConsultationRepository $consultationRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $patient = $user->getPatient();

        $consultations = $consultationRepository->findBy(['patient' => $patient], ['consultationDate' => 'DESC']);

        return $this->render('patient/consultations.html.twig', [
            'consultations' => $consultations,
        ]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/rdv', name: 'app_patient_rdv_space', methods: ['GET'])]
    public function rdvSpace(
        Request $request,
        RendezVousRepository $rendezVousRepository,
        DoctorRepository $doctorRepository,
        AvailabilityRepository $availabilityRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $tab = (string) $request->query->get('tab', 'overview');
        $selectedDoctorId = (int) $request->query->get('doctor');

        $rendezVous = $rendezVousRepository->createQueryBuilder('r')
            ->leftJoin('r.doctor', 'doctor')
            ->addSelect('doctor')
            ->where('r.patient = :patient')
            ->setParameter('patient', $user)
            ->orderBy('r.appointmentDateTime', 'DESC')
            ->getQuery()
            ->getResult();

        $doctors = $doctorRepository->createQueryBuilder('d')
            ->leftJoin('d.user', 'u')
            ->addSelect('u')
            ->where('d.isCertified = 1')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();

        $availabilities = [];
        if ($selectedDoctorId > 0) {
            $doctorUser = null;
            foreach ($doctors as $doctor) {
                if ($doctor->getUser()->getId() === $selectedDoctorId) {
                    $doctorUser = $doctor->getUser();
                    break;
                }
            }

            if ($doctorUser !== null) {
                $availabilities = $availabilityRepository->findBy(
                    ['doctor' => $doctorUser],
                    ['dayOfWeek' => 'ASC', 'startTime' => 'ASC']
                );
            }
        }

        return $this->render('patient/rdv_space.html.twig', [
            'activeTab' => $tab,
            'rendezVous' => $rendezVous,
            'doctors' => $doctors,
            'availabilities' => $availabilities,
            'selectedDoctorId' => $selectedDoctorId,
        ]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/rdv/cabinet/reserver', name: 'app_patient_rdv_cabinet_book', methods: ['POST'])]
    public function bookCabinetRdv(
        Request $request,
        UserRepository $userRepository,
        RendezVousRepository $rendezVousRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $patient */
        $patient = $this->getUser();

        if (!$this->isCsrfTokenValid('book_cabinet_rdv', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'cabinet']);
        }

        $doctorUserId = (int) $request->request->get('doctor_id');
        $appointmentAtRaw = (string) $request->request->get('appointment_at');
        $duration = max(10, (int) $request->request->get('duration', 30));
        $reason = trim((string) $request->request->get('reason'));

        $doctorUser = $userRepository->find($doctorUserId);
        if (!$doctorUser || !$doctorUser->getDoctor() || !$doctorUser->getDoctor()->isCertified()) {
            $this->addFlash('error', 'Médecin invalide.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'cabinet']);
        }

        try {
            $appointmentAt = new \DateTimeImmutable($appointmentAtRaw);
        } catch (\Throwable) {
            $this->addFlash('error', 'Date de rendez-vous invalide.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'cabinet']);
        }

        if ($appointmentAt <= new \DateTimeImmutable()) {
            $this->addFlash('error', 'Le rendez-vous doit être planifié dans le futur.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'cabinet']);
        }

        if ($this->hasRdvConflict($rendezVousRepository, $doctorUser, $appointmentAt)) {
            $this->addFlash('error', 'Le médecin a déjà un rendez-vous sur ce créneau.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'cabinet']);
        }

        if ($this->hasRdvConflict($rendezVousRepository, $patient, $appointmentAt)) {
            $this->addFlash('error', 'Vous avez déjà un rendez-vous sur ce créneau.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'cabinet']);
        }

        $rdv = new RendezVous();
        $rdv->setDoctor($doctorUser);
        $rdv->setPatient($patient);
        $rdv->setAppointmentDateTime(\DateTime::createFromImmutable($appointmentAt));
        $rdv->setDuration($duration);
        $rdv->setStatus(StatusRDVEnum::PENDING);
        $rdv->setConsultationType(ConsultationTypeEnum::IN_PERSON);
        $rdv->setReason($reason === '' ? null : $reason);
        $rdv->setReminderSent(false);

        $entityManager->persist($rdv);
        $entityManager->flush();

        $this->addFlash('success', 'Rendez-vous cabinet créé.');
        return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'cabinet']);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/rdv/en-ligne/reserver', name: 'app_patient_rdv_online_book', methods: ['POST'])]
    public function bookOnlineRdv(
        Request $request,
        UserRepository $userRepository,
        AvailabilityRepository $availabilityRepository,
        RendezVousRepository $rendezVousRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $patient */
        $patient = $this->getUser();

        if (!$this->isCsrfTokenValid('book_online_rdv', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online']);
        }

        $doctorUserId = (int) $request->request->get('doctor_id');
        $appointmentAtRaw = (string) $request->request->get('appointment_at');
        $duration = max(10, (int) $request->request->get('duration', 30));
        $reason = trim((string) $request->request->get('reason'));

        $doctorUser = $userRepository->find($doctorUserId);
        if (!$doctorUser || !$doctorUser->getDoctor() || !$doctorUser->getDoctor()->isCertified()) {
            $this->addFlash('error', 'Médecin invalide.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online']);
        }

        try {
            $appointmentAt = new \DateTimeImmutable($appointmentAtRaw);
        } catch (\Throwable) {
            $this->addFlash('error', 'Date de rendez-vous invalide.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online']);
        }

        if ($appointmentAt <= new \DateTimeImmutable()) {
            $this->addFlash('error', 'Le rendez-vous doit être planifié dans le futur.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online']);
        }

        if (!$this->doctorIsAvailableAt($doctorUser, $appointmentAt, $availabilityRepository)) {
            $this->addFlash('error', 'Ce médecin n\'est pas disponible sur ce créneau.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online', 'doctor' => $doctorUserId]);
        }

        if ($this->hasRdvConflict($rendezVousRepository, $doctorUser, $appointmentAt)) {
            $this->addFlash('error', 'Le médecin a déjà un rendez-vous sur ce créneau.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online', 'doctor' => $doctorUserId]);
        }

        if ($this->hasRdvConflict($rendezVousRepository, $patient, $appointmentAt)) {
            $this->addFlash('error', 'Vous avez déjà un rendez-vous sur ce créneau.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online', 'doctor' => $doctorUserId]);
        }

        $rdv = new RendezVous();
        $rdv->setDoctor($doctorUser);
        $rdv->setPatient($patient);
        $rdv->setAppointmentDateTime(\DateTime::createFromImmutable($appointmentAt));
        $rdv->setDuration($duration);
        $rdv->setStatus(StatusRDVEnum::PENDING);
        $rdv->setConsultationType(ConsultationTypeEnum::ONLINE);
        $rdv->setReason($reason === '' ? null : $reason);
        $rdv->setReminderSent(false);

        $entityManager->persist($rdv);
        $entityManager->flush();

        $this->addFlash('success', 'Rendez-vous en ligne créé.');
        return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online', 'doctor' => $doctorUserId]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/rdv/{id}/payer-online', name: 'app_patient_rdv_online_pay', methods: ['POST'])]
    public function payOnlineRdv(
        Request $request,
        RendezVous $rendezVous,
        ConsultationRepository $consultationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($rendezVous->getPatient()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('pay_online_rdv_' . $rendezVous->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online']);
        }

        if ($rendezVous->getConsultationType() !== ConsultationTypeEnum::ONLINE) {
            $this->addFlash('error', 'Paiement en ligne disponible uniquement pour les RDV en ligne.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online']);
        }

        if ($rendezVous->getStatus() === StatusRDVEnum::CANCELLED || $rendezVous->getStatus() === StatusRDVEnum::NO_SHOW) {
            $this->addFlash('error', 'Ce rendez-vous ne peut pas être payé.');
            return $this->redirectToRoute('app_patient_rdv_space', ['tab' => 'online']);
        }

        $consultation = $consultationRepository->createQueryBuilder('c')
            ->where('c.patient = :patient')
            ->andWhere('c.doctor = :doctor')
            ->andWhere('c.consultationDate = :date')
            ->andWhere('c.type = :type')
            ->andWhere('c.isDeleted = false')
            ->setParameter('patient', $user->getPatient())
            ->setParameter('doctor', $rendezVous->getDoctor()?->getDoctor())
            ->setParameter('date', $rendezVous->getAppointmentDateTime())
            ->setParameter('type', 'online')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$consultation) {
            $consultation = new \App\Entity\Consultation();
            $consultation->setPatient($user->getPatient());
            $consultation->setDoctor($rendezVous->getDoctor()?->getDoctor());
            $consultation->setConsultationDate($rendezVous->getAppointmentDateTime());
            $consultation->setType('online');
            $consultation->setStatus('confirmed');
            $consultation->setIsDeleted(false);

            $entityManager->persist($consultation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_paiement_consultation', ['id' => $consultation->getId()]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/factures', name: 'app_patient_factures', methods: ['GET'])]
    public function factures(FactureRepository $factureRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $factures = $factureRepository->createQueryBuilder('f')
            ->leftJoin('f.paiement', 'p')
            ->addSelect('p')
            ->leftJoin('p.consultation', 'c')
            ->addSelect('c')
            ->where('p.patient = :patient')
            ->setParameter('patient', $user)
            ->orderBy('f.dateEmission', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('patient/factures.html.twig', [
            'factures' => $factures,
        ]);
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/evaluations', name: 'app_patient_evaluations', methods: ['GET', 'POST'])]
    public function evaluations(
        Request $request,
        ConsultationRepository $consultationRepository,
        SatisfactionRepository $satisfactionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $patient = $user->getPatient();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('submit_evaluation', (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_patient_evaluations');
            }

            $consultationId = (int) $request->request->get('consultation_id');
            $score = (int) $request->request->get('score');
            $commentaire = trim((string) $request->request->get('commentaire'));

            if ($score < 1 || $score > 5) {
                $this->addFlash('error', 'La note doit être entre 1 et 5.');
                return $this->redirectToRoute('app_patient_evaluations');
            }

            $consultation = $consultationRepository->find($consultationId);
            if (!$consultation || $consultation->getPatient() !== $patient) {
                $this->addFlash('error', 'Consultation invalide.');
                return $this->redirectToRoute('app_patient_evaluations');
            }

            if ($consultation->getSatisfaction()) {
                $this->addFlash('error', 'Cette consultation est déjà évaluée.');
                return $this->redirectToRoute('app_patient_evaluations');
            }

            $satisfaction = new Satisfaction();
            $satisfaction->setConsultation($consultation);
            $satisfaction->setPatient($user);
            $satisfaction->setScore($score);
            $satisfaction->setCommentaire($commentaire === '' ? null : $commentaire);

            $entityManager->persist($satisfaction);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre évaluation.');
            return $this->redirectToRoute('app_patient_evaluations');
        }

        $consultations = $consultationRepository->createQueryBuilder('c')
            ->leftJoin('c.doctor', 'd')
            ->addSelect('d')
            ->where('c.patient = :patient')
            ->andWhere('c.doctor IS NOT NULL')
            ->andWhere('LOWER(c.status) IN (:statuses)')
            ->setParameter('patient', $patient)
            ->setParameter('statuses', ['completed', 'confirmed', 'terminee', 'terminée'])
            ->orderBy('c.consultationDate', 'DESC')
            ->getQuery()
            ->getResult();

        $existingEvaluations = $satisfactionRepository->createQueryBuilder('s')
            ->leftJoin('s.consultation', 'c')
            ->addSelect('c')
            ->where('s.patient = :patient')
            ->setParameter('patient', $user)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('patient/evaluations.html.twig', [
            'consultations' => $consultations,
            'existingEvaluations' => $existingEvaluations,
        ]);
    }

    private function hasRdvConflict(RendezVousRepository $rendezVousRepository, User $user, \DateTimeImmutable $appointmentAt): bool
    {
        $count = $rendezVousRepository->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('(r.doctor = :user OR r.patient = :user)')
            ->andWhere('r.appointmentDateTime = :appointmentAt')
            ->andWhere('r.status != :cancelled')
            ->setParameter('user', $user)
            ->setParameter('appointmentAt', \DateTime::createFromImmutable($appointmentAt))
            ->setParameter('cancelled', StatusRDVEnum::CANCELLED)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    private function doctorIsAvailableAt(
        User $doctorUser,
        \DateTimeImmutable $appointmentAt,
        AvailabilityRepository $availabilityRepository
    ): bool {
        $availabilities = $availabilityRepository->findBy(['doctor' => $doctorUser]);
        if ($availabilities === []) {
            return false;
        }

        $appointmentDay = $this->dayOfWeekFromDate($appointmentAt);
        $appointmentDate = $appointmentAt->format('Y-m-d');
        $appointmentTime = $appointmentAt->format('H:i:s');

        foreach ($availabilities as $availability) {
            if ($availability->getDayOfWeek() !== $appointmentDay) {
                continue;
            }

            if ($availability->getStartDate() && $appointmentDate < $availability->getStartDate()->format('Y-m-d')) {
                continue;
            }
            if ($availability->getEndDate() && $appointmentDate > $availability->getEndDate()->format('Y-m-d')) {
                continue;
            }

            $start = $availability->getStartTime()?->format('H:i:s');
            $end = $availability->getEndTime()?->format('H:i:s');
            if ($start === null || $end === null) {
                continue;
            }

            if ($appointmentTime >= $start && $appointmentTime < $end) {
                return true;
            }
        }

        return false;
    }

    private function dayOfWeekFromDate(\DateTimeInterface $date): DayOfWeekEnum
    {
        return match ((int) $date->format('N')) {
            1 => DayOfWeekEnum::MONDAY,
            2 => DayOfWeekEnum::TUESDAY,
            3 => DayOfWeekEnum::WEDNESDAY,
            4 => DayOfWeekEnum::THURSDAY,
            5 => DayOfWeekEnum::FRIDAY,
            6 => DayOfWeekEnum::SATURDAY,
            default => DayOfWeekEnum::SUNDAY,
        };
    }
}
