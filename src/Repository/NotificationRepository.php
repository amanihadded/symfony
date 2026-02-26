<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @return Notification[]
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countUnread(): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.isRead = :isRead')
            ->setParameter('isRead', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markAllAsRead(): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', ':isRead')
            ->setParameter('isRead', true)
            ->where('n.isRead = :unread')
            ->setParameter('unread', false)
            ->getQuery()
            ->execute();
    }
}
