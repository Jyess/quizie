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
    public function modifierQuiz($idQuiz, QuizRepository $quizRepository)
    {
        $quiz = $quizRepository->find($idQuiz);

        $question = new Question();
        $questionForm = $this->createForm(QuestionType::class, $question);

        return $this->render('quiz/creer_questions.html.twig', [
            'quiz' => $quiz,
            'questionFormulaire' => $questionForm->createView()
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
     * @Route("/form-question", name="quiz_genererFormQuestion")
     */
    public function genererFormQuestion(Request $request)
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $question = new Question();
            $questionForm = $this->createForm(QuestionType::class, $question);

            return $this->render('quiz/form_question.html.twig', [
                'questionFormulaire' => $questionForm->createView()
            ]);
        }

        throw new AccessDeniedException();
    }

    /**
     * @Route("/save-question/{idQuiz}", name="quiz_enregistrerQuestion", options = { "expose" = true })
     * @param Request $request
     */
    public function enregistrerQuestion(Request $request, QuizRepository $repository, $idQuiz)
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $question = new Question();
            $questionForm = $this->createForm(QuestionType::class, $question);

            $questionForm->handleRequest($request);

            dump($question);

            if ($questionForm->isSubmitted() && $questionForm->isValid()) {
                $quiz = $repository->find($idQuiz);
                $question->setQuiz($quiz);

                $entityMangager = $this->getDoctrine()->getManager();
                $entityMangager->persist($question);
                $entityMangager->flush();

                return new JsonResponse(['idQuestion' => $question->getId()], 201);
            }

            return $this->render('quiz/form_question.html.twig', [
                'questionFormulaire' => $questionForm->createView()
            ]);
        }

        // return new JsonResponse(['code' => 406], 406);
        throw new AccessDeniedException();
    }

    /**
     * @Route("/edit-form-question/{idQuestion}", name="quiz_genererFormEditQuestion")
     */
    public function genererFormEditQuestion($idQuestion, Request $request, QuestionRepository $questionRepository, EntityManagerInterface $entityManager)
    {
        // if ($request->isMethod(Request::METHOD_POST)) {
        //     $question = $questionRepository->find($idQuestion);

        //     $questionForm = $this->createForm(QuestionType::class, $question);

        //     return $this->render('quiz/form_question.html.twig', [
        //         'questionFormulaire' => $questionForm->createView()
        //     ]);
        // }

        if (!$request->isMethod(Request::METHOD_POST)) {
            throw new AccessDeniedException();
        }

        $question = $entityManager->getRepository(Question::class)->find($idQuestion);
        if ($question === null) {
            throw $this->createNotFoundException('No question found for id ' . $idQuestion);
        }

        $originalReponses = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($question->getReponses() as $reponse) {
            $originalReponses->add($reponse);
        }

        $editForm = $this->createForm(QuestionType::class, $question);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // remove the relationship between the tag and the Task
            foreach ($originalReponses as $reponse) {
                if (false === $question->getReponses()->contains($reponse)) {
                    // remove the Task from the Tag
                    // $reponse->getQuestions()->removeElement($reponse);

                    // if it was a many-to-one relationship, remove the relationship like this
                    $reponse->setQuestion(null);

                    $entityManager->persist($reponse);

                    // if you wanted to delete the Tag entirely, you can also do that
                    // $entityManager->remove($tag);
                }
            }

            $entityManager->persist($reponse);
            $entityManager->flush();

            // redirect back to some edit page
            // return $this->redirectToRoute('task_edit', ['id' => $id]);
            return $this->render('quiz/form_question.html.twig', [
                'questionFormulaire' => $editForm->createView()
            ]);
        }
        return $this->render('quiz/form_question.html.twig', [
            'questionFormulaire' => $editForm->createView()
        ]);
    }
}
