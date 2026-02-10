<?php

namespace App\Controller;

use App\Entity\Availability;
use App\Form\AvailabilityType;
use App\Repository\AvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/availability')]
final class AvailabilityController extends AbstractController
{
    #[Route(name: 'app_availability_index', methods: ['GET'])]
    public function index(Request $request, AvailabilityRepository $availabilityRepository): Response
    {
        $search = trim((string) $request->query->get('search'));
        $sortBy = (string) $request->query->get('sortBy', 'dayOfWeek');
        $sortOrder = (string) $request->query->get('sortOrder', 'ASC');
        $recurringFilter = (string) $request->query->get('recurring');
        $doctorFilter = (string) $request->query->get('doctor');

        // Build query
        $qb = $availabilityRepository->createQueryBuilder('a')
            ->leftJoin('a.doctor', 'doctor')
            ->addSelect('doctor');

        // Search
        if ($search !== '') {
            $qb->andWhere('doctor.username LIKE :search OR doctor.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filter by recurring
        if ($recurringFilter !== '') {
            $qb->andWhere('a.recurring = :recurring')
                ->setParameter('recurring', $recurringFilter === '1');
        }

        // Filter by doctor
        if ($doctorFilter !== '') {
            $qb->andWhere('a.doctor = :doctor')
                ->setParameter('doctor', $doctorFilter);
        }

        // Sorting
        $validSortFields = ['dayOfWeek', 'startTime', 'endTime', 'doctor', 'recurring'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'dayOfWeek';
        }
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy('a.' . $sortBy, $sortOrder);

        $availabilities = $qb->getQuery()->getResult();

        return $this->render('availability/index.html.twig', [
            'availabilities' => $availabilities,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'recurringFilter' => $recurringFilter,
            'doctorFilter' => $doctorFilter,
        ]);
    }

    #[Route('/api', name: 'app_availability_api_index', methods: ['GET'])]
    public function apiIndex(Request $request, AvailabilityRepository $availabilityRepository): JsonResponse
    {
        $search = $request->query->get('search', '');
        $doctorId = $request->query->get('doctorId', '');

        $qb = $availabilityRepository->createQueryBuilder('a')
            ->leftJoin('a.doctor', 'doctor');

        if ($search) {
            $qb->andWhere('doctor.username LIKE :search OR doctor.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($doctorId) {
            $qb->andWhere('a.doctor = :doctorId')
                ->setParameter('doctorId', $doctorId);
        }

        $results = $qb->getQuery()->getArrayResult();

        return $this->json($results);
    }

    #[Route('/new', name: 'app_availability_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $availability = new Availability();
        $form = $this->createForm(AvailabilityType::class, $availability);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($availability);
            $entityManager->flush();

            return $this->redirectToRoute('app_availability_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('availability/new.html.twig', [
            'availability' => $availability,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_availability_show', methods: ['GET'])]
    public function show(Availability $availability): Response
    {
        return $this->render('availability/show.html.twig', [
            'availability' => $availability,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_availability_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Availability $availability, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AvailabilityType::class, $availability);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_availability_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('availability/edit.html.twig', [
            'availability' => $availability,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_availability_delete', methods: ['POST'])]
    public function delete(Request $request, Availability $availability, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$availability->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($availability);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_availability_index', [], Response::HTTP_SEE_OTHER);
    }
}
