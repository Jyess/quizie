<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Reponse;
use App\Form\DemandeCleAccesType;
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

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($quiz);
            $entityManager->flush();

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
        $user = $this->getUser();
        $isQuizOwner = $quizRepository->findOneBy(["id" => $idQuiz, "utilisateurCreateur" => $user->getId()]);

        if ($isQuizOwner) {
            $quiz = $quizRepository->find($idQuiz);

            $question = new Question();
            $questionForm = $this->createForm(QuestionType::class, $question);

            $isAvailable = $this->verifPlageHoraire($idQuiz, $quizRepository);

            return $this->render('quiz/creer_questions.html.twig', [
                'quiz' => $quiz,
                'questionFormulaire' => $questionForm->createView(),
                'isAvailable' => $isAvailable
            ]);
        }

        throw new AccessDeniedException();
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
     * @Route("/quiz", name="quiz_tousLesQuiz")
     */
    public function tousLesQuiz(QuizRepository $quizRepository)
    {
        $lesQuiz = $quizRepository->findAllWithQuestions();

        return $this->render('quiz/tous_les_quiz.html.twig', [
            'lesQuiz' => $lesQuiz
        ]);
    }

    /**
     * Vérifie si le quiz est protege par une cle d'acces et affiche la page correspondante.
     * @Route("/quiz/{idQuiz}", name="quiz_demandeAccesQuiz")
     */
    public function demandeAccesQuiz($idQuiz, Request $request, QuizRepository $quizRepository, ReponseRepository $reponseRepository)
    {
        $isOwner = false;
        if ($user = $this->getUser()) {
            $isOwner = $quizRepository->findBy(['id' => $idQuiz, 'utilisateurCreateur' => $user->getId()]);
        }

        $isKeyProtected = $quizRepository->hasKey($idQuiz);
        $leQuiz = $quizRepository->findQuestionsWithAnswers($idQuiz);

        return $this->render('quiz/faire_un_quiz.html.twig', [
            'leQuiz' => $leQuiz,
            'canAccess' => $isOwner
        ]);
    }

    /**
     * Verifie si la cle d'acces est correcte et affiche la quiz si c'est la cas.
     * @Route("/afficher-quiz/{idQuiz}", name="quiz_afficherQuiz")
     */
    public function afficherQuiz($idQuiz, Request $request, QuizRepository $quizRepository)
    {
        $isOwner = false;
        if ($user = $this->getUser()) {
            $isOwner = $quizRepository->findBy(['id' => $idQuiz, 'utilisateurCreateur' => $user->getId()]);
        }

        if ($request->isMethod(Request::METHOD_POST) || $isOwner) {
            //verif de la clé d'acces
            $cleAccesSaisie = $request->request->get('cleAcces');
            $cleAccesCorrecte = $quizRepository->findBy(['cleAcces' => $cleAccesSaisie, 'id' => $idQuiz]);

            //si c'est le créateur qui veut afficher son quiz, pas besoin de verif, sinon on verif la cle
            if ($isOwner) {
                $leQuiz = $quizRepository->find($idQuiz);

                return $this->render('quiz/faire_un_quiz.html.twig', [
                    'leQuiz' => $leQuiz,
                    'canAccess' => $isOwner
                ]);
            } else if ($cleAccesCorrecte) {
                $leQuiz = $quizRepository->findQuestionsWithAnswers($idQuiz);
                $isAvailable = $this->verifPlageHoraire($leQuiz->getId(), $quizRepository);

                return $this->render('quiz/faire_un_quiz.html.twig', [
                    'leQuiz' => $leQuiz,
                    'isAvailable' => $isAvailable,
                    'canAccess' => true
                ]);
            } else {
                return new JsonResponse(['error' => 'wrong pwd'], 200);
            }
        }

        // return new JsonResponse(['code' => 406], 406);
        throw new AccessDeniedException();
    }

    /**
     * @Route("/recuperer-questions/{idQuiz}", name="quiz_recupererQuestionsDejaCreees")
     */
    public function recupererQuestionsDejaCreees($idQuiz, QuestionRepository $questionRepository, QuizRepository $quizRepository)
    {
        $quiz = $quizRepository->find($idQuiz);
        $questions = $questionRepository->findBy(["quiz" => $quiz]);

        $idsQuestions = [];
        foreach ($questions as $question) {
            $idsQuestions[] = $question->getId();
        }

        return new JsonResponse(['idsQuestions' => $idsQuestions], 201);
    }

    /**
     * @Route("/manage-question/{idQuiz}/{idQuestion}", name="quiz_manageQuestion", options = { "expose" = true }, defaults={"idQuestion"=null})
     */
    public function manageQuestion($idQuiz, $idQuestion, Request $request, QuizRepository $quizRepository, QuestionRepository $questionRepository)
    {
        $isOwner = false;
        if ($user = $this->getUser()) {
            $isOwner = $quizRepository->findBy(['id' => $idQuiz, 'utilisateurCreateur' => $user->getId()]);
        }

        if ($request->isMethod(Request::METHOD_POST) && $isOwner) {
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

            return $this->render('quiz/form_question.html.twig', [
                'questionFormulaire' => $questionForm->createView(),
                'question' => $uneQuestion
            ]);
        }

        // return new JsonResponse(['code' => 406], 406);
        throw new AccessDeniedException();
    }

    /**
     * @Route("/delete-question/{idQuiz}/{idQuestion}", name="quiz_deleteQuestion")
     */
    public function deleteQuestion($idQuiz, $idQuestion, Request $request, QuizRepository $quizRepository, QuestionRepository $questionRepository)
    {
        $isOwner = false;
        if ($user = $this->getUser()) {
            $isOwner = $quizRepository->findBy(['id' => $idQuiz, 'utilisateurCreateur' => $user->getId()]);
        }

        if ($request->isMethod(Request::METHOD_GET) && $isOwner) {
            $question = $questionRepository->find($idQuestion);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($question);
            $entityManager->flush();

            return new JsonResponse(['code' => '200'], 200);
        }

        // return new JsonResponse(['code' => 406], 406);
        throw new AccessDeniedException();
    }

    /**
     * @Route("/delete-quiz/{idQuiz}", name="quiz_deleteQuiz")
     */
    public function deleteQuiz($idQuiz, QuizRepository $quizRepository)
    {
        $isOwner = false;
        if ($user = $this->getUser()) {
            $isOwner = $quizRepository->findBy(['id' => $idQuiz, 'utilisateurCreateur' => $user->getId()]);
        }

        if ($isOwner) {
            $quizASupprimer = $quizRepository->find($idQuiz);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($quizASupprimer);
            $entityManager->flush();

            $mesQuiz = $quizRepository->findBy(['utilisateurCreateur' => $this->getUser()]);

            return $this->render('quiz/tous_mes_quiz.html.twig', [
                'mesQuiz' => $mesQuiz
            ]);
        }

        // return new JsonResponse(['code' => 406], 406);
        throw new AccessDeniedException();
    }

    private function verifPlageHoraire($idQuiz, QuizRepository $quizRepository) {
        date_default_timezone_set('Europe/Paris');

        $quiz = $quizRepository->find($idQuiz);

        $start = $quiz->getPlageHoraireDebut()->format("Y-m-d H:i:s");
        $end = $quiz->getPlageHoraireFin()->format("Y-m-d H:i:s");
        $currentTime = date("Y-m-d H:i:s");

        if ($start <= $currentTime && $currentTime <= $end) {
            return true;
        }

        return false;
    }
}
