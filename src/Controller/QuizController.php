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
     * @return Response
     */
    public function modifierQuiz($idQuiz, QuizRepository $quizRepository)
    {
        if (!$this->exist($idQuiz)) {
            throw new NotFoundHttpException();
        }

        //vérifie que le user connecté est bien le créateur
        if ($this->isOwner($idQuiz)) {
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
     * @Route("/quiz/{idQuiz}", name="quiz_afficherQuiz")
     */
    public function afficherQuiz($idQuiz, QuizRepository $quizRepository, Request $request)
    {
        //si le quiz n'existe pas on renvoie erreur 404
        if (!$this->exist($idQuiz)) {
            throw new NotFoundHttpException();
        }

        //verif si la personne connecté est le créateur du quiz
        $isOwner = $this->isOwner($idQuiz);

        //verif si le quiz est protégé par une clé d'accès
        $isKeyProtected = $quizRepository->hasAccessKey($idQuiz);

        //verif si le quiz est disponible ou pas
        $quizAvailable = $this->verifPlageHoraire($idQuiz, $quizRepository);

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
     */
    public function deleteQuestion($idQuiz, $idQuestion, Request $request, QuizRepository $quizRepository, QuestionRepository $questionRepository)
    {
        if (!$this->exist($idQuiz)) {
            throw new NotFoundHttpException();
        }

        //vérifie que le user connecté est bien le créateur
        if (!$request->isMethod(Request::METHOD_GET) && !$this->isOwner($idQuiz, $idQuestion)) {
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
     */
    public function recupererQuestionsDejaCreees($idQuiz, Request $request, QuestionRepository $questionRepository, QuizRepository $quizRepository)
    {
        //vérifie que le user connecté est bien le créateur
        if (!$request->isMethod(Request::METHOD_POST) && !$this->isOwner($idQuiz)) {
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
     */
    public function manageQuestion($idQuiz, $idQuestion, Request $request, QuizRepository $quizRepository, QuestionRepository $questionRepository)
    {
        //vérifie que le user connecté est bien le créateur
        if (!$request->isMethod(Request::METHOD_POST) || !$this->isOwner($idQuiz, $idQuestion)) {
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
     */
    public function deleteQuiz($idQuiz, Request $request, QuizRepository $quizRepository)
    {
        //vérifie que le user connecté est bien le créateur
        if (!$request->isMethod(Request::METHOD_DELETE) || !$this->isOwner($idQuiz)) {
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
     * Vérifie si la date et l'heure actuelle soit bien comprise dans l'intervalle de la plage horaire d'un quiz.
     */
    private function verifPlageHoraire($idQuiz, QuizRepository $quizRepository)
    {
        //met la timezone à paris
        date_default_timezone_set('Europe/Paris');

        //récupère le quiz
        $quiz = $quizRepository->find($idQuiz);

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
    private function isOwner($idQuiz, $idQuestion = null)
    {
        if ($idQuestion) {
            $questionRepository = $this->getDoctrine()->getRepository(Question::class);
            //si c'est pas vide et qu'on a une question c bon
            return !empty($questionRepository->isQuestionOwner($idQuestion, $idQuiz));
        }

        return $this->isQuizOwner($idQuiz);
    }

    /**
     * Vérifie si l'utilisateur est bien le créateur du quiz.
     *
     * @param $idQuiz
     * @return Quiz[]|bool|object[]
     */
    private function isQuizOwner($idQuiz)
    {
        $quizRepository = $this->getDoctrine()->getRepository(Quiz::class);

        if ($user = $this->getUser()) {
            //pas vide donc owner ok
            return !empty($quizRepository->findBy(['id' => $idQuiz, 'utilisateurCreateur' => $user]));
        }

        return false;
    }

    /**
     * Vérifie si un quiz existe.
     *
     * @param $idQuiz
     * @return Quiz|object|null
     */
    private function exist($idQuiz)
    {
        $quizRepository = $this->getDoctrine()->getRepository(Quiz::class);
        return $quizRepository->find($idQuiz);
    }
}
