<?php

namespace App\Repository;

use App\Entity\NotificationLogs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationLogs>
 */
class NotificationLogsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationLogs::class);
    }

    //    /**
    //     * @return NotificationLogs[] Returns an array of NotificationLogs objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?NotificationLogs
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
