<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findByArticle(Article $article): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.utilisateur', 'u')
            ->addSelect('u')
            ->where('c.article = :article')
            ->setParameter('article', $article)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentComments(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.article', 'a')
            ->leftJoin('c.utilisateur', 'u')
            ->addSelect('a', 'u')
            ->orderBy('c.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countComments(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}