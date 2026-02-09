<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Consultation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/chat')]
final class ChatController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/send/{consultationId}', name: 'app_chat_send', methods: ['POST'])]
    public function send(
        int $consultationId,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $consultation = $entityManager->getRepository(Consultation::class)->find($consultationId);

        if (!$consultation) {
            return new JsonResponse(['error' => 'Consultation non trouvée'], 404);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getPatient()->getId() !== $user->getId() 
            && $consultation->getMedecin()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $message = trim((string) $request->request->get('message'));

        if ($message === '') {
            return new JsonResponse(['error' => 'Message vide'], 400);
        }

        $senderRole = $consultation->getMedecin()->getId() === $user->getId() ? 'MEDECIN' : 'PATIENT';

        $chat = new Chat();
        $chat->setConsultation($consultation);
        $chat->setMessage($message);
        $chat->setSenderRole($senderRole);

        $entityManager->persist($chat);
        $entityManager->flush();

        return new JsonResponse([
            'status' => 'ok',
            'message' => $message,
            'senderRole' => $senderRole,
            'createdAt' => $chat->getCreatedAt()->format('H:i'),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/messages/{consultationId}', name: 'app_chat_messages', methods: ['GET'])]
    public function messages(
        int $consultationId,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $consultation = $entityManager->getRepository(Consultation::class)->find($consultationId);

        if (!$consultation) {
            return new JsonResponse(['error' => 'Consultation non trouvée'], 404);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getPatient()->getId() !== $user->getId() 
            && $consultation->getMedecin()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $messages = [];
        foreach ($consultation->getMessages() as $chat) {
            $messages[] = [
                'message' => $chat->getMessage(),
                'senderRole' => $chat->getSenderRole(),
                'createdAt' => $chat->getCreatedAt()->format('H:i'),
            ];
        }

        return new JsonResponse(['messages' => $messages]);
    }
}