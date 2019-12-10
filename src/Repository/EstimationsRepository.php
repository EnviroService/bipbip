<?php

namespace App\Repository;

use App\Entity\Estimations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Estimations|null find($id, $lockMode = null, $lockVersion = null)
 * @method Estimations|null findOneBy(array $criteria, array $orderBy = null)
 * @method Estimations[]    findAll()
 * @method Estimations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstimationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Estimations::class);
    }

    // /**
    //  * @return Estimations[] Returns an array of Estimations objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Estimations
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
