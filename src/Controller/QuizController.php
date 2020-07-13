<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Reponse;
use App\Form\QuizType;
use App\Form\QuestionType;
use App\Form\ReponseType;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Repository\ReponseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QuizController extends AbstractController
{
    /**
     * @Route("/creer-quiz", name="quiz_creerQuiz")
     * @param Request $request
     * @return Response
     */
    public function creerQuiz(Request $request)
    {
        $quiz = new Quiz();
        $quizForm = $this->createForm(QuizType::class, $quiz);
        $quizForm->handleRequest($request);

        if ($quizForm->isSubmitted() && $quizForm->isValid()) {
            //si l'état est privé
            if ($request->get("quiz")['etat']) {
                $quiz->setCleAcces($quiz->generateRandomString());
            }

            //set l'utilisateyur créateur du quiz
            $quiz->setUtilisateurCreateur($this->getUser());

            $entityMangager = $this->getDoctrine()->getManager();
            $entityMangager->persist($quiz);
            $entityMangager->flush();

            return $this->redirectToRoute("quiz_modifierQuiz", [
                'idQuiz' => $quiz->getId()
            ]);
        }

        //redirige vers la même page de création de quiz pour modif data non valides
        return $this->render('quiz/creer_quiz.html.twig', [
            'quiz_formulaire' => $quizForm->createView()
        ]);
    }

    /**
     * @Route("/modifier-quiz/{idQuiz}", name="quiz_modifierQuiz")
     * @param $idQuiz
     * @return Response
     */
    public function modifierQuiz($idQuiz, QuizRepository $quizRepository, QuestionRepository $questionRepository)
    {
        $quiz = $quizRepository->find($idQuiz);
        $questions = $questionRepository->findBy(["quiz" => $quiz]);

        if ($questions) {

        }

        $question = new Question();
        $questionForm = $this->createForm(QuestionType::class, $question);

        return $this->render('quiz/creer_questions.html.twig', [
            'quiz' => $quiz,
            'questionFormulaire' => $questionForm->createView(),
            'questions' => $questions
        ]);
    }

    /**
     * @Route("/mes-quiz", name="quiz_voirTousMesQuiz")
     */
    public function voirTousMesQuiz(QuizRepository $quizRepository)
    {
        $mesQuiz = $quizRepository->findBy(['utilisateurCreateur' => $this->getUser()]);

        return $this->render('quiz/tous_mes_quiz.html.twig', [
            'mesQuiz' => $mesQuiz
        ]);
    }

    /**
     * @Route("/manage-question/{idQuiz}/{idQuestion}", name="quiz_manageQuestion", options = { "expose" = true }, defaults={"idQuestion"=null})
     */
    public function manageQuestion($idQuiz, $idQuestion, Request $request, QuizRepository $quizRepository, QuestionRepository $questionRepository)
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $uneQuestion = new Question();
            $quiz = $quizRepository->find($idQuiz);

            $questionsDejaCreees = $questionRepository->findBy(["quiz" => $quiz]);

            if ($idQuestion) $uneQuestion = $questionRepository->find($idQuestion);

            $questionForm = $this->createForm(QuestionType::class, $uneQuestion);
            $questionForm->handleRequest($request);

            if ($questionForm->isSubmitted() && $questionForm->isValid()) {
                $uneQuestion->setQuiz($quiz);

                $entityMangager = $this->getDoctrine()->getManager();
                $entityMangager->persist($uneQuestion);
                $entityMangager->flush();

                return new JsonResponse(['idQuestion' => $uneQuestion->getId()], 201);
            }

            return $this->render('quiz/form_question.html.twig', [
                'questionFormulaire' => $questionForm->createView(),
                'question' => $uneQuestion,
                'questionsDejaCreees' => $questionsDejaCreees
            ]);
        }

        // return new JsonResponse(['code' => 406], 406);
        throw new AccessDeniedException();
    }
}
