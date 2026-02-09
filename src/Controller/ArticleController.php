<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\ArticleLike;
use App\Entity\Specialite;
use App\Entity\Tag;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\SpecialiteRepository;
use App\Repository\TagRepository;
use App\Repository\ArticleLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_forum_index')]
    public function index(
        ArticleRepository $articleRepository,
        SpecialiteRepository $specialiteRepository,
        TagRepository $tagRepository,
        Request $request
    ): Response {
        $query = $request->query->get('q');
        $specialiteId = $request->query->get('specialite');
        $tagId = $request->query->get('tag');
        $sortBy = $request->query->get('sort', 'date');

        $specialite = $specialiteId ? $specialiteRepository->find($specialiteId) : null;
        $tag = $tagId ? $tagRepository->find($tagId) : null;

        $articles = $articleRepository->searchArticles($query, $specialite, $tag, $sortBy);

        return $this->render('forum/index.html.twig', [
            'articles' => $articles,
            'specialites' => $specialiteRepository->findAll(),
            'popularTags' => $tagRepository->findMostUsedTags(10),
            'query' => $query,
            'selectedSpecialite' => $specialite,
            'selectedTag' => $tag,
            'sortBy' => $sortBy,
        ]);
    }

    #[Route('/article/{id}', name: 'app_forum_article_show')]
    public function show(
        Article $article,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($article->getStatut() !== 'publie') {
            throw $this->createNotFoundException('Article non disponible');
        }

        // Incrémenter le nombre de vues
        $article->incrementVues();
        $entityManager->flush();

        // Formulaire de commentaire
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                $this->addFlash('error', 'Vous devez être connecté pour commenter.');
                return $this->redirectToRoute('app_login');
            }

            $comment->setArticle($article);
            $comment->setUtilisateur($this->getUser());

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès !');
            return $this->redirectToRoute('app_forum_article_show', ['id' => $article->getId()]);
        }

        return $this->render('forum/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView(),
        ]);
    }

    #[Route('/specialite/{id}', name: 'app_forum_specialite')]
    public function specialite(
        Specialite $specialite,
        ArticleRepository $articleRepository
    ): Response {
        $articles = $articleRepository->findBySpecialite($specialite);

        return $this->render('forum/specialite.html.twig', [
            'specialite' => $specialite,
            'articles' => $articles,
        ]);
    }

    #[Route('/tag/{id}', name: 'app_forum_tag')]
    public function tag(
        Tag $tag,
        ArticleRepository $articleRepository
    ): Response {
        $articles = $articleRepository->findByTag($tag);

        return $this->render('forum/tag.html.twig', [
            'tag' => $tag,
            'articles' => $articles,
        ]);
    }

    #[Route('/article/{id}/like', name: 'app_forum_article_like', methods: ['POST'])]
    public function like(
        Article $article,
        EntityManagerInterface $entityManager,
        ArticleLikeRepository $likeRepository,
        Request $request
    ): Response {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('like_article', $request->request->get('_token'))) {
            $this->addFlash('error', 'Erreur de sécurité. Veuillez réessayer.');
            return $this->redirectToRoute('app_forum_article_show', ['id' => $article->getId()]);
        }

        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour aimer un article.');
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();
        $existingLike = $likeRepository->findOneByArticleAndUser($article, $user);

        if ($existingLike) {
            // Unlike
            $entityManager->remove($existingLike);
            $message = 'Vous n\'aimez plus cet article.';
        } else {
            // Like
            $like = new ArticleLike();
            $like->setArticle($article);
            $like->setUtilisateur($user);
            $entityManager->persist($like);
            $message = 'Vous aimez cet article !';
        }

        $entityManager->flush();
        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_forum_article_show', ['id' => $article->getId()]);
    }
}