<?php

namespace App\Controller;

use App\Repository\DoctorDocumentRepository;
use App\Repository\DoctorRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[IsGranted('ROLE_PATIENT')]
    #[Route('/dashboard/patient', name: 'app_patient_dashboard')]
    public function patientDashboard(): Response
    {
        return $this->render('dashboard/patient_dashboard.html.twig');
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/dashboard/doctor', name: 'app_doctor_dashboard')]
    public function doctorDashboard(Request $request, UserRepository $userRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $doctor = $user?->getDoctor();
        if ($doctor && !$doctor->isCertified()) {
            return $this->redirectToRoute('app_doctor_verification');
        }

        $search = trim((string) $request->query->get('q'));
        $patients = [];
        if ($search !== '') {
            $patients = $userRepository->createQueryBuilder('u')
                ->leftJoin('u.roles', 'r')
                ->addSelect('r')
                ->where('r.name = :role')
                ->andWhere('u.username LIKE :q OR u.email LIKE :q')
                ->setParameter('role', 'ROLE_PATIENT')
                ->setParameter('q', '%' . $search . '%')
                ->orderBy('u.username', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('dashboard/doctor_dashboard.html.twig', [
            'search' => $search,
            'patients' => $patients,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/dashboard/admin', name: 'app_admin_dashboard')]
    public function adminDashboard(
        UserRepository $userRepository,
        DoctorRepository $doctorRepository,
        DoctorDocumentRepository $doctorDocumentRepository
    ): Response
    {
        $totalUsers = $userRepository->count([]);
        $verifiedDoctors = $doctorRepository->count(['isCertified' => true]);
        $pendingVerifications = $doctorDocumentRepository->count(['status' => 'pending']);

        return $this->render('dashboard/admin_dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'verifiedDoctors' => $verifiedDoctors,
            'pendingVerifications' => $pendingVerifications,
            'totalAppointments' => 0,
        ]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/dashboard/doctor/appointments', name: 'app_doctor_appointments')]
    public function doctorAppointments(): Response
    {
        return $this->render('dashboard/doctor_appointments.html.twig');
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/dashboard/doctor/patients', name: 'app_doctor_patients')]
    public function doctorPatients(): Response
    {
        return $this->render('dashboard/doctor_patients.html.twig');
    }
}
