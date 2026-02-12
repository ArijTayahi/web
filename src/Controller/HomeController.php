<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        if ($this->getUser()) {
            // Redirect to appropriate dashboard based on role
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_admin_dashboard');
            } elseif ($this->isGranted('ROLE_PHYSICIAN')) {
                return $this->redirectToRoute('app_doctor_dashboard');
            } elseif ($this->isGranted('ROLE_PATIENT')) {
                return $this->redirectToRoute('app_patient_dashboard');
            }
        }

        return $this->render('home/index.html.twig');
    }

    #[Route('/appointments', name: 'app_appointments')]
    public function appointments(): Response
    {
        return $this->redirectToRoute('app_home');
    }
}
