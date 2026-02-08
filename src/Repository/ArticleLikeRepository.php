<?php

namespace App\Repository;

use App\Entity\ArticleLike;
use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleLike>
 */
class ArticleLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleLike::class);
    }

    public function findOneByArticleAndUser(Article $article, User $user): ?ArticleLike
    {
        return $this->createQueryBuilder('al')
            ->where('al.article = :article')
            ->andWhere('al.utilisateur = :user')
            ->setParameter('article', $article)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countLikes(): int
    {
        return $this->createQueryBuilder('al')
            ->select('COUNT(al.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}