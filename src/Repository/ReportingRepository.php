<?php

namespace App\Repository;

use App\Entity\Reporting;
use DateTime;
use DateInterval;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Reporting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reporting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reporting[]    findAll()
 * @method Reporting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reporting::class);
    }

    public function findByEstimatedYesterday()
    {
        $yesterday = new DateTime('-1 day');
        $qb = $this->createQueryBuilder('r')
            ->where('r.datereport > :yesterday')
            ->andWhere('r.reportype = :reportype')
            ->orderBy('r.datereport', 'ASC')
            ->setParameter('reportype', 'tobecollected')
            ->setParameter('yesterday', $yesterday->format('Y-m-d H:i:s'));

        $query = $qb->getQuery();

        return $query->execute();
    }

    public function findByCollectedYesterday()
    {
        $yesterday = new DateTime('-1 day');
        $qb = $this->createQueryBuilder('r')
            ->where('r.datereport > :yesterday')
            ->andWhere('r.reportype = :reportype')
            ->orderBy('r.datereport', 'ASC')
            ->setParameter('reportype', 'collected')
            ->setParameter('yesterday', $yesterday->format('Y-m-d H:i:s'));

        $query = $qb->getQuery();

        return $query->execute();
    }

    // /**
    //  * @return Reporting[] Returns an array of Reporting objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reporting
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
