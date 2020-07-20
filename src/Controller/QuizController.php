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
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Repository\ReponseRepository;
use App\Repository\ResultatRepository;
use App\Service\QuizService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * @throws NonUniqueResultException
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

            $isAvailable = $quizService->verifPlageHoraire($idQuiz);

            return $this->render('question/creer_questions.html.twig', [
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
     * @throws NonUniqueResultException
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
     * @Route("/delete-quiz/{idQuiz}", name="quiz_deleteQuiz")
     * @param $idQuiz
     * @param QuizRepository $quizRepository
     * @param QuizService $quizService
     * @return Response
     * @throws NonUniqueResultException
     */
    public function deleteQuiz($idQuiz, QuizRepository $quizRepository, QuizService $quizService)
    {
        if (!$quizService->exist($idQuiz)) {
            throw new NotFoundHttpException();
        }

        //vérifie que le user connecté est bien le créateur
        if (!$quizService->isOwner($idQuiz)) {
            throw new AccessDeniedException();
        }

        $quizASupprimer = $quizRepository->find($idQuiz);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($quizASupprimer);
        $entityManager->flush();

        return $this->redirectToRoute('quiz_voirTousMesQuiz');
    }

    /**
     * @Route("/verif-quiz/{idQuiz}", name="quiz_verifQuiz")
     * @param Request $request
     * @param $idQuiz
     * @param QuizRepository $quizRepository
     * @param QuestionRepository $questionRepository
     * @param ReponseRepository $reponseRepository
     * @return JsonResponse
     */
    public function verifQuiz(Request $request, $idQuiz, QuizRepository $quizRepository, QuestionRepository $questionRepository, ReponseRepository $reponseRepository)
    {
        $resultat = new Resultat();
        $quiz = $quizRepository->find($idQuiz);

        if (!$request->isMethod(Request::METHOD_POST)) {
            throw new AccessDeniedException();
        }

        if (!$quiz) {
            throw new NotFoundHttpException();
        }

        $resultat->setQuiz($quiz);

        $score = 0;
        $questionsReponsesUser = $request->request->all();

        foreach ($questionsReponsesUser as $idQuestion => $idReponse) {

            $question = $questionRepository->find($idQuestion);
            $reponse = $reponseRepository->find($idReponse);

            //verif si la question appartient bien au quiz
            $questionBelongToQuiz = $questionRepository->findBy(['id' => $idQuestion, 'quiz' => $quiz]);
            //verif si la reponse appartient a la quesiton
            $reponseBelongToQuestion = $reponseRepository->findBy(['id' => $idReponse, 'question' => $question]);

            if (empty($questionBelongToQuiz) || empty($reponseBelongToQuestion)) {
                throw new AccessDeniedException();
            }

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

    /**
     * @Route("/stat/{idQuiz}", name="quiz_voirStat")
     * @param $idQuiz
     * @param Request $request
     * @param QuizRepository $quizRepository
     * @param ResultatRepository $resultatRepository
     * @param QuizService $quizService
     * @return Response
     * @throws NonUniqueResultException
     */
    public function voirStat($idQuiz, Request $request, QuizRepository $quizRepository, ResultatRepository $resultatRepository, QuizService $quizService)
    {
        //vérifie que le user connecté est bien le créateur
        if (!$quizService->isOwner($idQuiz)) {
            throw new AccessDeniedException();
        }

        $quiz = $quizRepository->find($idQuiz);

        $statArray = $quizService->statData($idQuiz);

        //si aucun resultat, on va direct sur la page des stat
        if (!$statArray) {
            return $this->render('quiz/stat_quiz.html.twig', [
                'nbResultats' => 0
            ]);
        }

        return $this->render('quiz/stat_quiz.html.twig', [
            'leQuiz' => $quiz,
            'quizAvecQuestionsReponses' => $statArray["quizAvecQuestionsReponses"],
            'nbResultats' => $statArray["nbResultats"],
            'scoreMoyen' => $statArray["scoreMoyen"],
            'mediane' => $statArray["mediane"],
            'nbReponseQuiz' => $statArray["nbReponsesParQuiz"],
            'idsReponsesRepondues' => $statArray["idsReponsesRepondues"]
        ]);
    }

    /**
     * @Route("/export-resultats/{idQuiz}", name="quiz_exportResultatsCSV")
     * @param $idQuiz
     * @param QuizRepository $quizRepository
     * @param ResultatRepository $resultatRepository
     * @param QuizService $quizService
     * @return Response
     * @throws NonUniqueResultException
     */
    public function exportResultatsCSV($idQuiz, QuizRepository $quizRepository, ResultatRepository $resultatRepository, QuizService $quizService)
    {
        //vérifie que le user connecté est bien le créateur
        if (!$quizService->isOwner($idQuiz)) {
            throw new AccessDeniedException();
        }

        $user = $this->getUser();
        $quiz = $quizRepository->find($idQuiz);

        $statArray = $quizService->statData($idQuiz);

        $filename = "Résultats du Quiz #" . $quiz->getId() . " - " . $user->getNom();

        $response = new Response();

        $response->headers->set('Content-type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.csv";');
        $response->sendHeaders();

        $data = array();

        //headers quiz
        $data = $quizService->addRow($data, array(
            "Nom du quiz",
            "Nombre de résultats",
            "Score moyen",
            "Médiane des scores"
        ));

        //data quiz
        $data = $quizService->addRow($data, array(
            $quiz->getIntitule(),
            $statArray["nbResultats"],
            $statArray["scoreMoyen"],
            $statArray["mediane"]
        ));

        //blank row
        $data = $quizService->addRow($data, array(" "));

        //headers question reponse
        $data = $quizService->addRow($data, array(
            "Question",
            "Reponse juste",
            "Reponse fausse",
            "Reponse fausse",
            "Reponse fausse"
        ));

        foreach ($statArray["quizAvecQuestionsReponses"]->getQuestions() as $question) {
            $questionsReponses = array();

            array_push($questionsReponses, $question->getIntitule());

            foreach ($question->getReponses() as $reponse) {
                $intituleReponse = $reponse->getIntitule();

                foreach ($statArray["nbReponsesParQuiz"] as $nombre) {
                    if ($nombre["id"] == $reponse->getId()) {
                        $intituleReponse .= " (" . $nombre[1] . ")";
                    }
                }

                array_push($questionsReponses, $intituleReponse);
            }

            //data question reponse
            $data = $quizService->addRow($data, $questionsReponses);
        }

        $quizService->exportCSV($data);

        return $response;
    }
}
