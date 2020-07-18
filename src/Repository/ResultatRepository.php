<?php

namespace App\Repository;

use App\Entity\Resultat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Resultat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resultat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resultat[]    findAll()
 * @method Resultat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResultatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resultat::class);
    }

    /**
     * @return Resultat[] Returns an array of Resultat objects
     */
    public function nbReponsesParQuiz($idQuiz)
    {
        return $this->createQueryBuilder('res')
            ->select('rep.id', 'count(rep.id)')
            ->join('res.reponses', 'rep')
            ->andWhere('res.quiz = :idQuiz')
            ->groupBy('rep.id')
            ->setParameter('idQuiz', $idQuiz)
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Resultat
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
