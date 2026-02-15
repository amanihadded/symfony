<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findPaginated(int $page = 1, int $limit = 10, ?string $search = null, ?int $categoryId = null, ?int $fournisseurId = null): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.fournisseur', 'f')
            ->addSelect('c', 'f')
            ->orderBy('p.createdAt', 'DESC');

        if ($search) {
            $qb->andWhere('p.libelle LIKE :search OR p.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($categoryId) {
            $qb->andWhere('c.id = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        if ($fournisseurId) {
            $qb->andWhere('f.id = :fournisseurId')
               ->setParameter('fournisseurId', $fournisseurId);
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return new Paginator($qb, true);
    }

    /** @return Product[] */
    public function findLowStock(int $threshold = 5): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.stock <= :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('p.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalStockValue(): float
    {
        return (float) $this->createQueryBuilder('p')
            ->select('SUM(p.price * p.stock)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return array<array{category: string, count: int}> */
    public function countByCategory(): array
    {
        return $this->createQueryBuilder('p')
            ->select('c.nom as category, COUNT(p.id) as count')
            ->leftJoin('p.category', 'c')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }

    /** @return Product[] */
    public function findRecentProducts(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
