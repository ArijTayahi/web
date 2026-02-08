<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/tags')]
class AdminTagController extends AbstractController
{
    #[Route('/', name: 'app_admin_tag_index')]
    public function index(TagRepository $tagRepository): Response
    {
        return $this->render('admin/tag/index.html.twig', [
            'tags' => $tagRepository->findAllOrderedByName(),
        ]);
    }

    #[Route('/new', name: 'app_admin_tag_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tag);
            $entityManager->flush();

            $this->addFlash('success', 'Tag créé avec succès !');
            return $this->redirectToRoute('app_admin_tag_index');
        }

        return $this->render('admin/tag/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_tag_edit')]
    public function edit(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Tag modifié avec succès !');
            return $this->redirectToRoute('app_admin_tag_index');
        }

        return $this->render('admin/tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_tag_delete', methods: ['POST'])]
    public function delete(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tag);
            $entityManager->flush();

            $this->addFlash('success', 'Tag supprimé avec succès !');
        }

        return $this->redirectToRoute('app_admin_tag_index');
    }
}