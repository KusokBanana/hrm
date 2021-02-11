<?php

namespace App\Repository;

use App\Entity\Relevance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Relevance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Relevance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Relevance[]    findAll()
 * @method Relevance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelevanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Relevance::class);
    }

    // /**
    //  * @return Relevance[] Returns an array of Relevance objects
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
    public function findOneBySomeField($value): ?Relevance
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
