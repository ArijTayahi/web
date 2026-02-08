<?php

namespace App\Controller;

use App\Entity\Specialite;
use App\Form\SpecialiteType;
use App\Repository\SpecialiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/specialites')]
class AdminSpecialiteController extends AbstractController
{
    #[Route('/', name: 'app_admin_specialite_index')]
    public function index(SpecialiteRepository $specialiteRepository): Response
    {
        return $this->render('admin/specialite/index.html.twig', [
            'specialites' => $specialiteRepository->findAllWithArticleCount(),
        ]);
    }

    #[Route('/new', name: 'app_admin_specialite_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $specialite = new Specialite();
        $form = $this->createForm(SpecialiteType::class, $specialite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($specialite);
            $entityManager->flush();

            $this->addFlash('success', 'Spécialité créée avec succès !');
            return $this->redirectToRoute('app_admin_specialite_index');
        }

        return $this->render('admin/specialite/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_specialite_edit')]
    public function edit(
        Request $request,
        Specialite $specialite,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(SpecialiteType::class, $specialite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Spécialité modifiée avec succès !');
            return $this->redirectToRoute('app_admin_specialite_index');
        }

        return $this->render('admin/specialite/edit.html.twig', [
            'specialite' => $specialite,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_specialite_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Specialite $specialite,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$specialite->getId(), $request->request->get('_token'))) {
            $entityManager->remove($specialite);
            $entityManager->flush();

            $this->addFlash('success', 'Spécialité supprimée avec succès !');
        }

        return $this->redirectToRoute('app_admin_specialite_index');
    }
}