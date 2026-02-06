<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ModuleAController extends AbstractController
{
    #[IsGranted('ROLE_PATIENT')]
    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function profileEdit(): Response
    {
        return $this->render('profile/edit_profile.html.twig');
    }

    #[IsGranted('ROLE_PATIENT')]
    #[Route('/patient/appointments', name: 'app_patient_appointments')]
    public function patientAppointments(): Response
    {
        return $this->render('patient/appointments.html.twig');
    }
}
