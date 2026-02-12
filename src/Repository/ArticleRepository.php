<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Specialite;
use App\Entity\Tag;
use App\Entity\Doctor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findPublishedArticles(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.statut = :statut')
            ->setParameter('statut', 'publie')
            ->orderBy('a.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedArticlesPaginated(int $page = 1, int $limit = 10): array
    {
        $query = $this->createQueryBuilder('a')
            ->leftJoin('a.auteur', 'auteur')
            ->leftJoin('a.specialite', 's')
            ->addSelect('auteur', 's')
            ->where('a.statut = :statut')
            ->setParameter('statut', 'publie')
            ->orderBy('a.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit);

        return $query->getQuery()->getResult();
    }

    public function countPublishedArticles(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.statut = :statut')
            ->setParameter('statut', 'publie')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findBySpecialite(Specialite $specialite): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.specialite = :specialite')
            ->andWhere('a.statut = :statut')
            ->setParameter('specialite', $specialite)
            ->setParameter('statut', 'publie')
            ->orderBy('a.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByTag(Tag $tag): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.tags', 't')
            ->where('t = :tag')
            ->andWhere('a.statut = :statut')
            ->setParameter('tag', $tag)
            ->setParameter('statut', 'publie')
            ->orderBy('a.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByDoctor(Doctor $doctor): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.auteur = :doctor')
            ->setParameter('doctor', $doctor)
            ->orderBy('a.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchArticles(?string $query, ?Specialite $specialite = null, ?Tag $tag = null, ?string $sortBy = 'date'): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.auteur', 'auteur')
            ->leftJoin('a.specialite', 's')
            ->addSelect('auteur', 's')
            ->where('a.statut = :statut')
            ->setParameter('statut', 'publie');

        if ($query) {
            $qb->andWhere('LOWER(a.titre) LIKE LOWER(:query) OR LOWER(a.contenu) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($specialite) {
            $qb->andWhere('a.specialite = :specialite')
                ->setParameter('specialite', $specialite);
        }

        if ($tag) {
            $qb->leftJoin('a.tags', 't')
                ->andWhere('t = :tag')
                ->setParameter('tag', $tag);
        }

        switch ($sortBy) {
            case 'vues':
                $qb->orderBy('a.nbVues', 'DESC');
                break;
            case 'likes':
                $qb->leftJoin('a.likes', 'l')
                    ->groupBy('a.id')
                    ->orderBy('COUNT(l.id)', 'DESC');
                break;
            case 'comments':
                $qb->leftJoin('a.comments', 'c')
                    ->groupBy('a.id')
                    ->orderBy('COUNT(c.id)', 'DESC');
                break;
            default:
                $qb->orderBy('a.dateCreation', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    public function findMostPopular(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.statut = :statut')
            ->setParameter('statut', 'publie')
            ->orderBy('a.nbVues', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRecentArticles(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.statut = :statut')
            ->setParameter('statut', 'publie')
            ->orderBy('a.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getStatistics(): array
    {
        $total = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $publies = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.statut = :statut')
            ->setParameter('statut', 'publie')
            ->getQuery()
            ->getSingleScalarResult();

        $brouillons = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.statut = :statut')
            ->setParameter('statut', 'brouillon')
            ->getQuery()
            ->getSingleScalarResult();

        $totalVues = $this->createQueryBuilder('a')
            ->select('SUM(a.nbVues)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'publies' => $publies,
            'brouillons' => $brouillons,
            'totalVues' => $totalVues ?? 0,
        ];
    }

    public function findArticlesGroupedByMonth(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT DATE_FORMAT(a.date_creation, '%Y-%m') as month, COUNT(a.id) as count
            FROM article a
            WHERE a.statut = 'publie'
            GROUP BY month
            ORDER BY month DESC
            LIMIT 12
        ";
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        return $result->fetchAllAssociative();
    }
}