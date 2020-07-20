<?php

namespace App\DataFixtures;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Reponse;
use App\Entity\Resultat;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;

class QuizFixtures extends Fixture
{
    public function load(\Doctrine\Persistence\ObjectManager $manager)
    {
        ini_set('memory_limit', '1G');

        $faker = \Faker\Factory::create('fr_FR');

        $batchSize = 10;

        //création des users
        for ($i = 0; $i < 200; $i++) {
            $utilisateur = new Utilisateur();

            $utilisateur->setNom($faker->lastName);
            $utilisateur->setPrenom($faker->firstName);
            $utilisateur->setEmail($faker->unique()->email);
            $utilisateur->setPassword(\password_hash('azerty', \PASSWORD_BCRYPT));

            $manager->persist($utilisateur);

            if (($i % $batchSize) === 0) {
                $manager->flush(); // Executes all updates.
                $manager->clear(); // Detaches all objects from Doctrine!
            }
        }

        //création des quiz
        for ($i = 0; $i < 10; $i++) {
            $quiz = new Quiz();

            $quiz->setIntitule($faker->text);
            $quiz->setPlageHoraireDebut($faker->dateTimeBetween('-1 month', 'now'));
            $quiz->setPlageHoraireFin($faker->dateTimeBetween('now', '+1 month'));
            $quiz->setCleAcces($quiz->generateRandomString());
            $quiz->setUtilisateurCreateur($manager->getRepository('App:Utilisateur')->find($faker->numberBetween(1,200)));

            $manager->persist($quiz);

            if (($i % $batchSize) === 0) {
                $manager->flush(); // Executes all updates.
                $manager->clear(); // Detaches all objects from Doctrine!
            }
        }

        $tousLesQuiz = $manager->getRepository('App:Quiz')->findAll();

        //création des questions
        for($quiz = 0; $quiz < count($tousLesQuiz); $quiz++) {
            for ($i = 0; $i < 10; $i++) {
                $question = new Question();

                $question->setIntitule($faker->text . " ?");
                $question->setQuiz($tousLesQuiz[$quiz]);
                $question->setNbPointsBonneReponse($faker->numberBetween(1, 50));
                $question->setNbPointsMauvaiseReponse($faker->numberBetween(-50, -1));

                $manager->persist($question);

                if (($i % $batchSize) === 0) {
                    $manager->flush();
                    $manager->clear(); // Detaches all objects from Doctrine!
                    $tousLesQuiz = $manager->getRepository('App:Quiz')->findAll();
                }
            }
        }

        $toutesLesQuestions = $manager->getRepository('App:Question')->findAll();

        //création des reponses
        for ($question = 0; $question < count($toutesLesQuestions); $question++) {

            $vraiFaux = true;

            //génère 2 à 4 reponses
            for ($i = 0; $i < $faker->numberBetween(2, 4); $i++) {
                $reponse = new Reponse();

                $reponse->setIntitule($faker->text);
                $reponse->setVraiFaux($vraiFaux);
                $reponse->setQuestion($toutesLesQuestions[$question]);

                $manager->persist($reponse);

                $vraiFaux = false;

                if (($i % $batchSize) === 0) {
                    $manager->flush();
                    $manager->clear(); // Detaches all objects from Doctrine!
                    $toutesLesQuestions = $manager->getRepository('App:Question')->findAll();
                    $tousLesQuiz = $manager->getRepository('App:Quiz')->findAll();
                }
            }
        }

        for ($quiz = 0; $quiz < count($tousLesQuiz); $quiz++) {
            for ($i = 0; $i < 5000; $i++) {
                $resultat = new Resultat();
                $score = 0;

                //pour chaque question du quiz
                foreach ($tousLesQuiz[$quiz]->getQuestions() as $question) {
                    $reponsesQuestion = array();
                    //pour chaque reponse d'une question
                    foreach ($question->getReponses() as $reponse) {
                        array_push($reponsesQuestion, $reponse);
                    }

                    $reponseSelected = $reponsesQuestion[rand(0,count($reponsesQuestion)-1)];

                    if ($reponseSelected->getVraiFaux()) {
                        $score += $question->getNbPointsBonneReponse();
                    } else {
                        $score += $question->getNbPointsMauvaiseReponse();
                    }

                    $resultat->addReponse($reponseSelected);
                }

                $resultat->setScore($score);
                $resultat->setQuiz($tousLesQuiz[$quiz]);

                $manager->persist($resultat);
            }
        }

        $manager->flush();
    }
}
