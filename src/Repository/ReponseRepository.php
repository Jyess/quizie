<?php

namespace App\Repository;

use App\Entity\Reponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reponse[]    findAll()
 * @method Reponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reponse::class);
    }

    // /**
    //  * @return Quiz[] Returns an array of Quiz objects
    //  */
    // public function findQuestionsWithAnswers($idQuiz)
    // {
    //     return $this->createQueryBuilder('reponse')
    //         ->join('reponse.question', 'question')
    //         ->join('question.quiz', 'quiz')
    //         ->andWhere('quiz.id = :idQuiz')
    //         ->setParameter('idQuiz', $idQuiz)
    //         ->getQuery()
    //         ->getResult();
    // }

    /*
    public function findOneBySomeField($value): ?Reponse
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
