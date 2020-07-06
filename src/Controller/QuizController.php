<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Form\CreateQuizType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
    /**
     * @Route("/creer-quiz", name="quiz_creerQuiz")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creerQuiz(Request $request)
    {
        $quizForm = $this->createForm(CreateQuizType::class);
        $quizForm->handleRequest($request);

        if ($quizForm->isSubmitted() && $quizForm->isValid()) {
            $quizData = $quizForm->getData();

            $quiz = new Quiz();
            $quiz->setIntitule($quizData['intitule']);
            $quiz->setPlageHoraireDebut($quizData['plageHoraireDebut']);
            $quiz->setPlageHoraireFin($quizData['plageHoraireFin']);
            $quiz->setCleAcces($quizData['cleAcces']);
            $quiz->setUtilisateurCreateur($this->getUser());

            $entityMangager = $this->getDoctrine()->getManager();
            $entityMangager->persist($quiz);
            $entityMangager->flush();
        }

        return $this->render('quiz/creer_quiz.html.twig', [
            'quiz_formulaire' => $quizForm->createView()
        ]);
    }
}
