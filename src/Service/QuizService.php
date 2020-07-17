<?php

namespace App\Service;

use App\Controller\SecurityController;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Utilisateur;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class QuizService
{
    private $quizRepository;

    private $questionRepository;

    private $utilisateur;

    /**
     * QuizService constructor.
     * @param $quizRepository
     * @param $questionRepository
     * @param $utilisateur
     */
    public function __construct(QuizRepository $quizRepository, QuestionRepository $questionRepository, Security $security)
    {
        $this->quizRepository = $quizRepository;
        $this->questionRepository = $questionRepository;
        $this->utilisateur = $security->getUser();
    }

    /**
     * @return mixed
     */
    public function getQuizRepository()
    {
        return $this->quizRepository;
    }

    /**
     * @param mixed $quizRepository
     */
    public function setQuizRepository($quizRepository): void
    {
        $this->quizRepository = $quizRepository;
    }

    /**
     * @return mixed
     */
    public function getQuestionRepository()
    {
        return $this->questionRepository;
    }

    /**
     * @param mixed $questionRepository
     */
    public function setQuestionRepository($questionRepository): void
    {
        $this->questionRepository = $questionRepository;
    }

    /**
     * @return mixed
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * @param mixed $utilisateur
     */
    public function setUtilisateur($utilisateur): void
    {
        $this->utilisateur = $utilisateur;
    }

    /**
     * Vérifie si la date et l'heure actuelle soit bien comprise dans l'intervalle de la plage horaire d'un quiz.
     */
    public function verifPlageHoraire($idQuiz)
    {
        //met la timezone à paris
        date_default_timezone_set('Europe/Paris');

        //récupère le quiz
        $quiz = $this->getQuizRepository()->find($idQuiz);

        //si y a pas de plage horaire début c'est public donc dispo tout le temps
        if (!$quiz->getPlageHoraireDebut()) {
            return true;
        }

        //récup les plages horaires et l'heure acteulle
        $start = $quiz->getPlageHoraireDebut()->format("Y-m-d H:i:s");
        $end = $quiz->getPlageHoraireFin()->format("Y-m-d H:i:s");
        $currentTime = date("Y-m-d H:i:s");

        if ($start <= $currentTime && $currentTime <= $end) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur actuel est bien celui qui a créé le quiz ou la question.
     *
     * @param $idQuiz
     * @param null $idQuestion
     * @return Quiz[]|bool|object[]
     */
    public function isOwner($idQuiz, $idQuestion = null)
    {
        if ($idQuestion) {
            //si c'est pas vide et qu'on a une question c bon
            return !empty($this->getQuestionRepository()->isQuestionOwner($idQuestion, $idQuiz));
        }

        return $this->isQuizOwner($idQuiz);
    }

    /**
     * Vérifie si l'utilisateur est bien le créateur du quiz.
     *
     * @param $idQuiz
     * @return Quiz[]|bool|object[]
     */
    public function isQuizOwner($idQuiz)
    {
        if ($user = $this->getUtilisateur()) {
            //pas vide donc owner ok
            return !empty($this->getQuizRepository()->findBy(['id' => $idQuiz, 'utilisateurCreateur' => $user]));
        }

        return false;
    }

    /**
     * Vérifie si un quiz existe.
     *
     * @param $idQuiz
     * @return Quiz|object|null
     */
    public function exist($idQuiz)
    {
        return $this->getQuizRepository()->find($idQuiz);
    }
}