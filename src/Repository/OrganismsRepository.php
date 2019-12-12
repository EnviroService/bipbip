<?php

namespace App\Repository;

use App\Entity\Organisms;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Organisms|null find($id, $lockMode = null, $lockVersion = null)
 * @method Organisms|null findOneBy(array $criteria, array $orderBy = null)
 * @method Organisms[]    findAll()
 * @method Organisms[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganismsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organisms::class);
    }

    // /**
    //  * @return Organisms[] Returns an array of Organisms objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Organisms
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
