<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Deals with connection, and user params
 */
class UserProfilController extends AbstractController
{
    /**
     * @Route("/preferences", name="preferences")
     */
    public function index()
    {
        return $this->render('user_profil/index.html.twig', [
            'controller_name' => 'UserProfilController',
        ]);
    }
}
