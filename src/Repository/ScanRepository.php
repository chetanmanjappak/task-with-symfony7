<?php

namespace App\Repository;

use App\Entity\Scan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Constants\Status;

/**
 * @extends ServiceEntityRepository<Scan>
 */
class ScanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scan::class);
    }
    /**
     * Find scan IDs with status 'SCANNING-IN-PROGRESS'.
     *
     * @return array
     */
    public function findScanningInProgressScanIds()
    {
        return $this->createQueryBuilder('sc')
            ->select('sc.ci_upload_id')
            ->where('sc.status = :status')
            ->setParameter('status', Status::SCANNING_IN_PROGRESS)
            ->getQuery()
            ->getScalarResult();
    }
}
