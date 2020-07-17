<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Reponse;
use App\Entity\Resultat;
use App\Form\DemandeCleAccesType;
use App\Form\QuestionnaireType;
use App\Form\QuizType;
use App\Form\QuestionType;
use App\Form\ReponseType;
use App\Form\ResultatType;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Repository\ReponseRepository;
use App\Service\QuizService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use http\QueryString;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * Affiche un formulaire et sauvegarde dans la base de données.
     * 
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
     * @param QuizRepository $quizRepository
     * @param QuizService $quizService
     * @return Response
     */
    public function modifierQuiz($idQuiz, QuizRepository $quizRepository, QuizService $quizService)
    {
        if (!$quizService->exist($idQuiz)) {
            throw new NotFoundHttpException();
        }

        //vérifie que le user connecté est bien le créateur
        if ($quizService->isOwner($idQuiz)) {
            $quiz = $quizRepository->find($idQuiz);

            $question = new Question();
            $questionForm = $this->createForm(QuestionType::class, $question);

            $isAvailable = $quizService->verifPlageHoraire($idQuiz, $quizRepository);

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
     * @param QuizRepository $quizRepository
     * @return Response
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
     * @param QuizRepository $quizRepository
     * @return Response
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
     * @Route("/quiz/{idQuiz}", name="quiz_afficherQuiz")
     * @param $idQuiz
     * @param QuizRepository $quizRepository
     * @param Request $request
     * @param QuizService $quizService
     * @return JsonResponse|Response
     */
    public function afficherQuiz($idQuiz, QuizRepository $quizRepository, Request $request, QuizService $quizService)
    {
        //si le quiz n'existe pas on renvoie erreur 404
        if (!$quizService->exist($idQuiz)) {
            throw new NotFoundHttpException();
        }

        //verif si la personne connecté est le créateur du quiz
        $isOwner = $quizService->isOwner($idQuiz);

        //verif si le quiz est protégé par une clé d'accès
        $isKeyProtected = $quizRepository->hasAccessKey($idQuiz);

        //verif si le quiz est disponible ou pas
        $quizAvailable = $quizService->verifPlageHoraire($idQuiz);

        //récup les questions reponses du quiz (si pas de questions, renvoie null)
        $quizAvecQuestionsReponses = $quizRepository->findQuestionsWithAnswers($idQuiz);

        //recup les data du quiz
        $leQuiz = $quizRepository->find($idQuiz);

        //recup en post la cle d'acces
        $cleAccesSaisie = $request->request->get('cleAcces');
        $cleAccesCorrecte = $quizRepository->findBy(['cleAcces' => $cleAccesSaisie, 'id' => $idQuiz]);

        //t'es le createur ?
        if (!$isOwner) {

            if (!$quizAvecQuestionsReponses) {
                throw new AccessDeniedException();
            }

            //quiz privé  et clé non saisie ? on demande l'acces
            if ($isKeyProtected && !$cleAccesSaisie) {
                return $this->render('quiz/demande_acces.html.twig', [
                    'leQuiz' => $leQuiz
                ]);

                //quiz privé et clé saisie incorrecte ? on envoie une erreur
            } else if ($isKeyProtected && !$cleAccesCorrecte) {
                return new JsonResponse(['error' => 'wrong pwd'], 200);
            }
        }

        //si on est le createur du quiz
        if ($request->isMethod(Request::METHOD_POST)) {
            return $this->render('quiz/faire_un_quiz.html.twig', [
                'quizAvecQuestionsReponses' => $quizAvecQuestionsReponses,
                'leQuiz' => $leQuiz,
                'quizAvailable' => $quizAvailable
            ]);
        }

        //va ici la premiere fois qu'on accede à un quiz
        //puis requete ajax qui va load cette meme route
        return $this->render('quiz/quiz_holder.html.twig', [
            'leQuiz' => $leQuiz
        ]);
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

    /**
     * @Route("/recuperer-questions/{idQuiz}", name="quiz_recupererQuestionsDejaCreees")
     * @param $idQuiz
     * @param Request $request
     * @param QuestionRepository $questionRepository
     * @param QuizRepository $quizRepository
     * @param QuizService $quizService
     * @return JsonResponse
     */
    public function recupererQuestionsDejaCreees($idQuiz, Request $request, QuestionRepository $questionRepository, QuizRepository $quizRepository, QuizService $quizService)
    {
        //vérifie que le user connecté est bien le créateur
        if (!$request->isMethod(Request::METHOD_POST) && !$quizService->isOwner($idQuiz)) {
            throw new AccessDeniedException();
        }

        $quiz = $quizRepository->find($idQuiz);
        $questions = $questionRepository->findBy(["quiz" => $quiz]);

        $idsQuestions = [];
        foreach ($questions as $question) {
            $idsQuestions[] = $question->getId();
        }

        return new JsonResponse(['idsQuestions' => $idsQuestions], 201);
    }

    /**
     * Modifie ou supprime une question.
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

        return $this->render('quiz/form_question.html.twig', [
            'questionFormulaire' => $questionForm->createView(),
            'question' => $uneQuestion
        ]);
    }

    /**
     * @Route("/delete-quiz/{idQuiz}", name="quiz_deleteQuiz")
     * @param $idQuiz
     * @param QuizRepository $quizRepository
     * @param QuizService $quizService
     * @return Response
     */
    public function deleteQuiz($idQuiz, QuizRepository $quizRepository, QuizService $quizService)
    {
        //vérifie que le user connecté est bien le créateur
        if (!$quizService->isOwner($idQuiz)) {
            throw new AccessDeniedException();
        }

        $quizASupprimer = $quizRepository->find($idQuiz);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($quizASupprimer);
        $entityManager->flush();

        $mesQuiz = $quizRepository->findBy(['utilisateurCreateur' => $this->getUser()]);

        return $this->render('quiz/tous_mes_quiz.html.twig', [
            'mesQuiz' => $mesQuiz
        ]);
    }

    /**
     * @Route("/verif-quiz/{idQuiz}", name="quiz_verifQuiz")
     * @param Request $request
     */
    public function verifQuiz(Request $request, $idQuiz, QuizRepository $quizRepository, QuestionRepository $questionRepository,ReponseRepository $reponseRepository)
    {
        $resultat = new Resultat();
        $quiz = $quizRepository->find($idQuiz);
        $resultat->setQuiz($quiz);

        $score = 0;
        $questionsReponsesUser = $request->request->all();

        foreach ($questionsReponsesUser as $idQuestion => $idReponse) {
            $question = $questionRepository->find($idQuestion);

            $reponse = $reponseRepository->find($idReponse);
            $resultat->addReponse($reponse);

            if ($reponse->getVraiFaux()) {
                $score += $question->getNbPointsBonneReponse();
            } else {
                $score += $question->getNbPointsMauvaiseReponse();
            }
        }

        $resultat->setScore($score);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($resultat);
        $entityManager->flush();

        return new JsonResponse(['data' => 'ok'], 200);
    }
}
