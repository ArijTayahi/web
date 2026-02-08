<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findMostUsedTags(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.articles', 'a')
            ->where('a.statut = :statut')
            ->setParameter('statut', 'publie')
            ->groupBy('t.id')
            ->orderBy('COUNT(a.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}