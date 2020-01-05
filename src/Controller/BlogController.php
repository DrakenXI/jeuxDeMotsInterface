<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * TODO relations
     */
    public function index()
    {
        $relations = ['toto', 'titi', 'tata', 'tutu', 'tete'];
        return $this->render('blog/index.html.twig', [
            'title' => 'Bienvenue',
            'relations' => $relations]);
    }
}
