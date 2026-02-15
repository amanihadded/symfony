<?php

namespace App\Repository;

use App\Entity\Fournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class FournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fournisseur::class);
    }

    public function findPaginated(int $page = 1, int $limit = 10, ?string $search = null): Paginator
    {
        $qb = $this->createQueryBuilder('f')
            ->orderBy('f.nom', 'ASC');

        if ($search) {
            $qb->andWhere('f.nom LIKE :search OR f.email LIKE :search OR f.adresse LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return new Paginator($qb, true);
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
