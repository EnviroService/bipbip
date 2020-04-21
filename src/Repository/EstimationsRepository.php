<?php

namespace App\Repository;

use App\Entity\Estimations;
use DateTime;
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

    public function findByUncollected()
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.isCollected = 0')
            ->andWhere('e.user is not null')
            ->orderBy('e.id', 'DESC');

        $query = $qb->getQuery();

        return $query->execute();
    }

    public function findByCollected()
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.isCollected = 1')
            ->orderBy('e.id', 'DESC');

        $query = $qb->getQuery();

        return $query->execute();
    }

    public function findByUnfinished()
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.user is null')
            ->orderBy('e.id', 'DESC');

        $query = $qb->getQuery();

        return $query->execute();
    }

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
