<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    public function getAll(){

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            "SELECT se
            FROM App\Entity\Session se"
        );
        return $query->execute();
    }

    // /**
    //  * @return Session[] Returns an array of Session objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Session
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findSessionsDispo() {
        $er = $this->getEntityManager()->createQuery(
            'SELECT se 
            FROM App\Entity\Session se 
            WHERE se.NbPlaces > COUNT(se.stagiaires) 
            ORDER BY se.intitule ASC'
        );
        return $er->getQuery()->getResult();
        /*$qb = $er->createQueryBuilder('se');
        return $qb->andWhere($qb->expr()->gt('se.NbPlaces',  $qb->expr()->count('se.stagiaires')))
                  
                  ->orderBy('se.intitule');*/
    }
}
