<?php

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\User;
use App\Entity\Vacancy;
use Assert\Assert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findOneByLogin(string $login): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.login = :login')
            ->setParameter('login', $login)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByToken(string $token): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function setCandidate(User $user, Candidate $candidate): void
    {
//        $user = $this->findOneByLogin($login);
        Assert::that($user->getCandidate())->null('User can\'t have more than one candidates');

        $this->getEntityManager()->createQueryBuilder()
            ->update(User::class, 'u')
            ->set('u.candidate', ':candidate')
            ->andWhere('u.login = :login')
            ->setParameter('login', $user->getUsername())
            ->setParameter('candidate', $candidate->getId())
            ->getQuery()->execute();
    }

    public function setVacancy(User $user, Vacancy $vacancy): void
    {
//        $user = $this->findOneByLogin($login);
        Assert::that($user->getVacancy())->null('User can\'t have more than one vacancy');

        $this->getEntityManager()->createQueryBuilder()
            ->update(User::class, 'u')
            ->set('u.vacancy', ':vacancy')
            ->andWhere('u.login = :login')
            ->setParameter('login', $user->getUsername())
            ->setParameter('vacancy', $vacancy->getId())
            ->getQuery()->execute();
    }
}
