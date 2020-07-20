<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Service\QuizService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    /**
     * Gère la création et modification des questions ainsi que leurs réponses.
     *
     * @Route("/manage-question/{idQuiz}/{idQuestion}", name="quiz_manageQuestion", options = { "expose" = true }, defaults={"idQuestion"=null})
     * @param $idQuiz
     * @param $idQuestion
     * @param Request $request
     * @param QuizRepository $quizRepository
     * @param QuestionRepository $questionRepository
     * @param QuizService $quizService
     * @return JsonResponse|Response
     */
    public function manageQuestion($idQuiz, $idQuestion, Request $request, QuizRepository $quizRepository, QuestionRepository $questionRepository, QuizService $quizService)
    {
        //vérifie que le user connecté est bien le créateur
        if (!$request->isMethod(Request::METHOD_POST) || !$quizService->isOwner($idQuiz, $idQuestion)) {
            throw new AccessDeniedException();
        }

        //reponses de la question dans la bdd
        $reponsesBD = new ArrayCollection();

        $uneQuestion = new Question();
        $quiz = $quizRepository->find($idQuiz);

        if ($idQuestion) {
            $uneQuestion = $questionRepository->find($idQuestion);

            foreach ($uneQuestion->getReponses() as $reponse) {
                $reponsesBD->add($reponse);
            }
        }

        $questionForm = $this->createForm(QuestionType::class, $uneQuestion);
        $questionForm->handleRequest($request);

        if ($questionForm->isSubmitted() && $questionForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $uneQuestion->setQuiz($quiz);

            foreach ($reponsesBD as $reponse) {
                $reponse->setQuestion($uneQuestion);
                if (!$uneQuestion->getReponses()->contains($reponse)) {
                    $reponse->setQuestion(null);
                    $uneQuestion->removeReponse($reponse);
                    $entityManager->persist($reponse);
                }
            }

            $entityManager->persist($uneQuestion);
            $entityManager->flush();

            return new JsonResponse(['idQuestion' => $uneQuestion->getId()], 201);
        }

        return $this->render('question/form_question.html.twig', [
            'questionFormulaire' => $questionForm->createView(),
            'question' => $uneQuestion
        ]);
    }

    /**
     * Récupère les ids des questions d'un quiz.
     *
     * @Route("/recuperer-questions/{idQuiz}", name="quiz_recupererQuestionsDejaCreees")
     * @param $idQuiz
     * @param Request $request
     * @param QuestionRepository $questionRepository
     * @param QuizRepository $quizRepository
     * @param QuizService $quizService
     * @return JsonResponse
     * @throws NonUniqueResultException
     */
    public function recupererQuestionsDejaCreees($idQuiz, Request $request, QuestionRepository $questionRepository, QuizRepository $quizRepository, QuizService $quizService)
    {
        //vérifie que le user connecté est bien le créateur
        if (!$request->isMethod(Request::METHOD_POST) && !$quizService->isOwner($idQuiz)) {
            throw new AccessDeniedException();
        }

        $idsQuestions = $questionRepository->getQuestionsIds($idQuiz);

        return new JsonResponse(['idsQuestions' => $idsQuestions], 201);
    }

    /**
     * @Route("/delete-question/{idQuiz}/{idQuestion}", name="quiz_deleteQuestion")
     * @param $idQuiz
     * @param $idQuestion
     * @param Request $request
     * @param QuestionRepository $questionRepository
     * @param QuizService $quizService
     * @return JsonResponse
     */
    public function deleteQuestion($idQuiz, $idQuestion, Request $request, QuestionRepository $questionRepository, QuizService $quizService)
    {
        if (!$quizService->exist($idQuiz)) {
            throw new NotFoundHttpException();
        }

        //vérifie que le user connecté est bien le créateur
        if (!$request->isMethod(Request::METHOD_GET) && !$quizService->isOwner($idQuiz, $idQuestion)) {
            throw new AccessDeniedException();
        }

        $question = $questionRepository->find($idQuestion);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($question);
        $entityManager->flush();

        return new JsonResponse(['code' => '200'], 200);
    }
}
