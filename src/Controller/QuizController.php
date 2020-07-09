<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Reponse;
use App\Form\QuizType;
use App\Form\QuestionType;
use App\Form\ReponseType;
use App\Repository\QuizRepository;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

//        $errors = $validator->validate($quiz);

//        if (count($errors) > 0) {
//dump($errors);
//        }

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

            return $this->redirectToRoute("quiz_voirQuiz", [
                'idQuiz' => $quiz->getId()
            ]);
        }

        //redirige vers la même page de création de quiz pour modif data non valides
        return $this->render('quiz/creer_quiz.html.twig', [
            'quiz_formulaire' => $quizForm->createView()
        ]);
    }

    /**
     * @Route("/voir-quiz/{idQuiz}", name="quiz_voirQuiz")
     * @param $idQuiz
     * @return Response
     */
    public function voirQuiz($idQuiz, QuizRepository $repository) {
        $quiz = $repository->find($idQuiz);

        $question = new Question();
        $questionForm = $this->createForm(QuestionType::class, $question);

        return $this->render('quiz/creer_questions.html.twig', [
            'quiz' => $quiz,
            'questionFormulaire' => $questionForm->createView()
        ]);
    }

    /**
     * @Route("/form-question", name="quiz_genererFormQuestion")
     */
    public function genererFormQuestion() {
        $question = new Question();
        $questionForm = $this->createForm(QuestionType::class, $question);

        $reponse = new Reponse();
        $reponseForm = $this->createForm(ReponseType::class, $reponse);

        return $this->render('quiz/form_question.html.twig', [
            'questionFormulaire' => $questionForm->createView(),
            'reponseFormulaire' => $reponseForm->createView()
        ]);
    }

    /**
     * @Route("/save-question", name="quiz_enregistrerQuestion")
     * @param Request $request
     */
    public function enregistrerQuestion(Request $request, QuizRepository $repository, ValidatorInterface $validator) {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {
            $questionData = $request->request->all()["question"];

            $question = new Question();
            $question->setIntitule($questionData["intitule"]);
            $question->setNbPointsBonneReponse($questionData["nbPointsBonneReponse"]);
            $question->setNbPointsMauvaiseReponse($questionData["nbPointsMauvaiseReponse"]);

            $quiz = $repository->find($questionData["quiz"]);
            $question->setQuiz($quiz);

            $errors = $validator->validate($question);

            if (count($errors) > 0) {
                $arrayErrors = [];
                foreach ($errors as $error) {
                    array_push($arrayErrors, $error->getMessage());
                }
                return new JsonResponse(['msg' => $arrayErrors]);
//                return $response->setStatusCode('400');
            }

            $entityMangager = $this->getDoctrine()->getManager();
            $entityMangager->persist($question);
            $entityMangager->flush();

            return $response->setStatusCode('200');
        }

        return $response->setStatusCode('401');
    }
}
