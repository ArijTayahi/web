<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\ArticleLikeRepository;
use App\Repository\SpecialiteRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/forum')]
class ForumDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_forum_dashboard')]
    public function dashboard(
        ArticleRepository $articleRepository,
        CommentRepository $commentRepository,
        ArticleLikeRepository $likeRepository,
        SpecialiteRepository $specialiteRepository,
        TagRepository $tagRepository
    ): Response {
        $articleStats = $articleRepository->getStatistics();
        $totalComments = $commentRepository->countComments();
        $totalLikes = $likeRepository->countLikes();

        $mostPopularArticles = $articleRepository->findMostPopular(5);
        $recentArticles = $articleRepository->findRecentArticles(5);
        $recentComments = $commentRepository->findRecentComments(10);
        
        $popularSpecialites = $specialiteRepository->findPopularSpecialites(5);
        $popularTags = $tagRepository->findMostUsedTags(10);
        
        $monthlyArticles = $articleRepository->findArticlesGroupedByMonth();

        return $this->render('admin/forum/dashboard.html.twig', [
            'articleStats' => $articleStats,
            'totalComments' => $totalComments,
            'totalLikes' => $totalLikes,
            'mostPopularArticles' => $mostPopularArticles,
            'recentArticles' => $recentArticles,
            'recentComments' => $recentComments,
            'popularSpecialites' => $popularSpecialites,
            'popularTags' => $popularTags,
            'monthlyArticles' => $monthlyArticles,
        ]);
    }
}