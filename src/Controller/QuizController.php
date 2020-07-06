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
        $quiz = new Quiz();
        $quizForm = $this->createForm(CreateQuizType::class, $quiz);
        $quizForm->handleRequest($request);

        if ($quizForm->isSubmitted() && $quizForm->isValid()) {
            //si l'état est privé
            if ($request->get("create_quiz")['etat']) {
                $quiz->setCleAcces($quiz->generateRandomString());
            }

            //set l'utilisateyur créateur du quiz
            $quiz->setUtilisateurCreateur($this->getUser());

            $entityMangager = $this->getDoctrine()->getManager();
            $entityMangager->persist($quiz);
            $entityMangager->flush();
        }

        //reset le formulaire si retour en arrière
//        $quizForm = $this->createForm(CreateQuizType::class);

        return $this->render('quiz/creer_quiz.html.twig', [
            'quiz_formulaire' => $quizForm->createView()
        ]);
    }
}
