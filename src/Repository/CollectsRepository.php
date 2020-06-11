<?php

namespace App\Repository;

use App\Entity\Collects;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Collects|null find($id, $lockMode = null, $lockVersion = null)
 * @method Collects|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collects[]    findAll()
 * @method Collects[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollectsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collects::class);
    }

    public function findByDateValid()
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.dateCollect > current_date()')
            ->orderBy('c.dateCollect', 'ASC');

        $query = $qb->getQuery();

        return $query->execute();
    }

    public function findByTomorowCollect()
    {
        $dayCollects = [];
        $interval = new DateInterval('P1D');
        $today = new DateTime('now');
        $tomorow = date_add($today, $interval)->format('Y-m-d');

        $qb = $this->createQueryBuilder('c')
            ->where('c.dateCollect >= :demain')
            ->setParameter('demain', $tomorow);

        $result = $qb->getQuery()->execute();

        foreach ($result as $collecte) {
            $date = $collecte->getDateCollect();
            $today = new DateTime();
            $diff = date_diff($date, $today)->d;

            if ($diff == 0) {
                array_push($dayCollects, $collecte);
            }
        }
        return $dayCollects;
    }

    // /**
    //  * @return Collects[] Returns an array of Collects objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Collects
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
