<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\SessionVisio;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/visio')]
final class VisioController extends AbstractController
{
    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/start/{id}', name: 'app_visio_start', methods: ['GET'])]
    public function start(Consultation $consultation, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if (!$consultation->getSessionVisio()) {
            $session = new SessionVisio();
            $session->setConsultation($consultation);
            $session->setStatus('STARTED');
            $session->setStartedAt(new \DateTime());

            $entityManager->persist($session);
            $entityManager->flush();
        } else {
            $session = $consultation->getSessionVisio();
            if ($session->getStatus() === 'CREATED') {
                $session->setStatus('STARTED');
                $session->setStartedAt(new \DateTime());
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('app_visio_room', ['roomId' => $session->getRoomId()]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/room/{roomId}', name: 'app_visio_room', methods: ['GET'])]
    public function room(string $roomId, EntityManagerInterface $entityManager): Response
    {
        $session = $entityManager->getRepository(SessionVisio::class)->findOneBy(['roomId' => $roomId]);

        if (!$session) {
            throw $this->createNotFoundException('Salle de visio introuvable.');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $consultation = $session->getConsultation();

        if ($consultation->getPatient()->getId() !== $user->getId() 
            && $consultation->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $isMedecin = $consultation->getMedecin()->getId() === $user->getId();

        return $this->render('visio/room.html.twig', [
            'session' => $session,
            'consultation' => $consultation,
            'isMedecin' => $isMedecin,
        ]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/end/{roomId}', name: 'app_visio_end', methods: ['POST'])]
    public function end(
        string $roomId,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $session = $entityManager->getRepository(SessionVisio::class)->findOneBy(['roomId' => $roomId]);

        if (!$session) {
            throw $this->createNotFoundException('Salle de visio introuvable.');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($session->getConsultation()->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('end_visio_' . $roomId, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_visio_room', ['roomId' => $roomId]);
        }

        $session->setStatus('ENDED');
        $session->setEndedAt(new \DateTime());

        $entityManager->flush();

        return $this->redirectToRoute('app_consultation_show', ['id' => $session->getConsultation()->getId()]);
    }
}