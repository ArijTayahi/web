<?php

namespace App\Controller;

use App\Entity\Ordonnance;
use App\Entity\User;
use App\Form\OrdonnanceType;
use App\Repository\OrdonnanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ordonnance')]
final class OrdonnanceController extends AbstractController
{
    #[IsGranted('ROLE_PATIENT')]
    #[Route('/', name: 'app_ordonnance_index', methods: ['GET'])]
    public function index(OrdonnanceRepository $ordonnanceRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $patient = $user->getPatient();
        $ordonnances = $ordonnanceRepository->createQueryBuilder('o')
            ->leftJoin('o.consultation', 'c')
            ->where('c.patient = :patient')
            ->setParameter('patient', $patient)
            ->getQuery()
            ->getResult();

        return $this->render('ordonnance/index.html.twig', [
            'ordonnances' => $ordonnances,
        ]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/doctor', name: 'app_ordonnance_doctor_index', methods: ['GET'])]
    public function doctorIndex(OrdonnanceRepository $ordonnanceRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $doctor = $user->getDoctor();
        $ordonnances = $ordonnanceRepository->findBy(['doctor' => $doctor]);

        return $this->render('ordonnance/doctor_index.html.twig', [
            'ordonnances' => $ordonnances,
        ]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/new/{consultationId}', name: 'app_ordonnance_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $consultationId, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $consultation = $entityManager->getRepository(\App\Entity\Consultation::class)->find($consultationId);
        if (!$consultation || $consultation->getDoctor() !== $user->getDoctor()) {
            throw $this->createAccessDeniedException();
        }

        $ordonnance = new Ordonnance();
        $ordonnance->setConsultation($consultation);
        $ordonnance->setDoctor($user->getDoctor());

        $form = $this->createForm(OrdonnanceType::class, $ordonnance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ordonnance);
            $entityManager->flush();

            return $this->redirectToRoute('app_ordonnance_doctor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ordonnance/new.html.twig', [
            'ordonnance' => $ordonnance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ordonnance_show', methods: ['GET'])]
    public function show(Ordonnance $ordonnance): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Check if user is physician and owns the prescription
        if ($this->isGranted('ROLE_PHYSICIAN') && $ordonnance->getDoctor() === $user->getDoctor()) {
            // Physician can view their own prescriptions
        }
        // Check if user is patient and owns the prescription
        elseif ($this->isGranted('ROLE_PATIENT') && $ordonnance->getConsultation()->getPatient() === $user->getPatient()) {
            // Patient can view their own prescriptions
        }
        else {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette ordonnance.');
        }

        return $this->render('ordonnance/show.html.twig', [
            'ordonnance' => $ordonnance,
        ]);
    }

    #[IsGranted('ROLE_DOCTOR')]
    #[Route('/{id}/edit', name: 'app_ordonnance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ordonnance $ordonnance, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($ordonnance->getDoctor() !== $user->getDoctor()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(OrdonnanceType::class, $ordonnance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ordonnance->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_ordonnance_doctor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ordonnance/edit.html.twig', [
            'ordonnance' => $ordonnance,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/{id}', name: 'app_ordonnance_delete', methods: ['POST'])]
    public function delete(Request $request, Ordonnance $ordonnance, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($ordonnance->getDoctor() !== $user->getDoctor()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$ordonnance->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ordonnance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ordonnance_doctor_index', [], Response::HTTP_SEE_OTHER);
    }
}
