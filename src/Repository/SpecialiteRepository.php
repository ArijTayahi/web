<?php

namespace App\Repository;

use App\Entity\Specialite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Specialite>
 */
class SpecialiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Specialite::class);
    }

    public function findAllWithArticleCount(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.articles', 'a')
            ->addSelect('COUNT(a.id) as HIDDEN articleCount')
            ->groupBy('s.id')
            ->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPopularSpecialites(int $limit = 5): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.articles', 'a')
            ->where('a.statut = :statut')
            ->setParameter('statut', 'publie')
            ->groupBy('s.id')
            ->orderBy('COUNT(a.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
