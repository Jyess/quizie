<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    /**
     * @Route("/se-connecter", name="utilisateur_connexion")
     */
    public function index()
    {
        return $this->render('utilisateur/connexion.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
}
