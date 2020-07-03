<?php

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="security_connexion")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('accueil_accueil');
        }

        $loginForm = $this->createForm(LoginType::class);

        // récupère les erreurs de login
        $error = $authenticationUtils->getLastAuthenticationError();

        // dernier username entrer par le user
        $last_email = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_email' => $last_email,
            'login' => $loginForm->createView()
        ]);
    }

    /**
     * @Route("/deconnexion", name="security_deconnexion")
     */
    public function logout()
    {
    }
}
