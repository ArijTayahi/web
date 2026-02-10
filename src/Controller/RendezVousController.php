<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rendez/vous')]
final class RendezVousController extends AbstractController
{
    #[Route(name: 'app_rendez_vous_index', methods: ['GET'])]
    public function index(Request $request, RendezVousRepository $rendezVousRepository): Response
    {
        $search = trim((string) $request->query->get('search'));
        $sortBy = (string) $request->query->get('sortBy', 'appointmentDateTime');
        $sortOrder = (string) $request->query->get('sortOrder', 'DESC');
        $statusFilter = (string) $request->query->get('status');
        $typeFilter = (string) $request->query->get('type');

        // Build query
        $qb = $rendezVousRepository->createQueryBuilder('rdv')
            ->leftJoin('rdv.doctor', 'doctor')
            ->leftJoin('rdv.patient', 'patient')
            ->addSelect('doctor', 'patient');

        // Search
        if ($search !== '') {
            $qb->andWhere('doctor.username LIKE :search OR patient.username LIKE :search OR rdv.reason LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filter by status
        if ($statusFilter !== '') {
            $qb->andWhere('rdv.status = :status')
                ->setParameter('status', $statusFilter);
        }

        // Filter by type
        if ($typeFilter !== '') {
            $qb->andWhere('rdv.consultationType = :type')
                ->setParameter('type', $typeFilter);
        }

        // Sorting
        $validSortFields = ['appointmentDateTime', 'doctor', 'patient', 'status', 'duration'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'appointmentDateTime';
        }
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        $qb->orderBy('rdv.' . $sortBy, $sortOrder);

        $rendez_vouses = $qb->getQuery()->getResult();

        return $this->render('rendez_vous/index.html.twig', [
            'rendez_vouses' => $rendez_vouses,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'statusFilter' => $statusFilter,
            'typeFilter' => $typeFilter,
        ]);
    }

    #[Route('/api', name: 'app_rendez_vous_api_index', methods: ['GET'])]
    public function apiIndex(Request $request, RendezVousRepository $rendezVousRepository): JsonResponse
    {
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');

        $qb = $rendezVousRepository->createQueryBuilder('rdv')
            ->leftJoin('rdv.doctor', 'doctor')
            ->leftJoin('rdv.patient', 'patient');

        if ($search) {
            $qb->andWhere('doctor.username LIKE :search OR patient.username LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $qb->andWhere('rdv.status = :status')
                ->setParameter('status', $status);
        }

        $results = $qb->getQuery()->getArrayResult();

        return $this->json($results);
    }

    #[Route('/new', name: 'app_rendez_vous_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rendezVou = new RendezVous();
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rendezVou);
            $entityManager->flush();

            return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rendez_vous/new.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rendez_vous_show', methods: ['GET'])]
    public function show(RendezVous $rendezVou): Response
    {
        return $this->render('rendez_vous/show.html.twig', [
            'rendez_vou' => $rendezVou,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rendez_vous_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rendez_vous/edit.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rendez_vous_delete', methods: ['POST'])]
    public function delete(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rendezVou->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rendezVou);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
    }
}
