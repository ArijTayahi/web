<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findPhysicians()
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')
            ->where('r.id = :roleId')
            ->andWhere('u.isActive = 1')
            ->setParameter('roleId', 10)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
