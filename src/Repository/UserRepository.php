<?php

namespace App\Repository;

use App\Entity\Search;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use \DateTime;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param UserInterface $user
     * @param string $newEncodedPassword
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }


    public function findSearch($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.lastname LIKE :val')
            ->setParameter('val', $value .'%')
            ->getQuery()
            ->getResult();
    }
  
    public function findCollectors($role)
    {

        $qb = $this->createQueryBuilder('u');
        $qb->select('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%');

        return $qb->getQuery()->getResult();
    }

    public function findOldUsers(DateTime $date)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('u')
            ->where('u.signinDate < :date')
            ->andWhere('u.signinDate != :oldDate')
            ->setParameter('date', $date)
            ->setParameter('oldDate', '1970-01-01');
        return $qb->getQuery()->getResult();
    }

    public function findFutureOldUsers()
    {
        $olddate = new DateTime('-35 month');
        $qb = $this->createQueryBuilder('u')
            ->where('u.signinDate < :olddate')
            ->setParameter('olddate', $olddate->format('Y-m-d H:i:s'));

        $query = $qb->getQuery();
        return $query->execute();
    }
}
