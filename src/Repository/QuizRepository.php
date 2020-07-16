<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Quiz|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quiz|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quiz[]    findAll()
 * @method Quiz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

    /**
     * @return Quiz[] Returns an array of Quiz objects
     */
    public function findAllWithQuestions()
    {
        return $this->createQueryBuilder('quiz')
            ->join('quiz.questions', 'question')
            ->where('quiz.id = :idQuiz')
            ->where('question > 0')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Quiz[] Returns an array of Quiz objects
     */
    public function hasAccessKey($idQuiz)
    {
        return $this->createQueryBuilder('quiz')
            ->select('quiz.cleAcces')
            ->where('quiz.id = :idQuiz')
            ->setParameter('idQuiz', $idQuiz)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Quiz[] Returns an array of Quiz objects
     */
    public function findQuestionsWithAnswers($idQuiz)
    {
        return $this->createQueryBuilder('quiz')
            ->join('quiz.questions', 'question')
            ->join('question.reponses', 'reponse')
            ->where('quiz.id = :idQuiz')
            ->setParameter('idQuiz', $idQuiz)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /*
    public function findOneBySomeField($value): ?Quiz
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
