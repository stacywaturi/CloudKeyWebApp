<?php

namespace App\Repository;

use App\Entity\Keys;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Keys|null find($id, $lockMode = null, $lockVersion = null)
 * @method Keys|null findOneBy(array $criteria, array $orderBy = null)
 * @method Keys[]    findAll()
 * @method Keys[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KeysRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Keys::class);
    }




    // /**
    //  * @return Keys[] Returns an array of Keys objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Keys
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
