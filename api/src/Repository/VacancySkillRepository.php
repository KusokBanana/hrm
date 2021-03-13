<?php

namespace App\Repository;

use App\Entity\VacancySkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VacancySkill|null find($id, $lockMode = null, $lockVersion = null)
 * @method VacancySkill|null findOneBy(array $criteria, array $orderBy = null)
 * @method VacancySkill[]    findAll()
 * @method VacancySkill[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VacancySkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VacancySkill::class);
    }

    // /**
    //  * @return VacancySkill[] Returns an array of Vacancy objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vacancy
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
