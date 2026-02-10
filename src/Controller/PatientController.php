<?php

namespace App\Controller;

use App\Repository\ConsultationRepository;
use App\Repository\DoctorRepository;
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
}
