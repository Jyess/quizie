<?php

namespace App\Repository;

use App\Entity\Question;
use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * @return Question Returns a Question object or null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isQuestionOwner($idQuestion, $idQuiz)
    {
        return $this->createQueryBuilder('question')
            ->join('question.quiz', 'quiz')
            ->where('question.id = :idQuestion')
            ->andWhere('quiz.id = :idQuiz')
            ->setParameter('idQuestion', $idQuestion)
            ->setParameter('idQuiz', $idQuiz)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Question Returns a Question object or null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getQuestionsIds($idQuiz)
    {
        return $this->createQueryBuilder('question')
            ->select('question.id')
            ->where('question.quiz = :idQuiz')
            ->setParameter('idQuiz', $idQuiz)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Question[] Returns an array of Question objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Question
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
