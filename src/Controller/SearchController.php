<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    function getHtmlContentFor(string $term){
      $page = file_get_contents("http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel=".$term."&rel=");
      return $page;
    }

    /**
     * @Route("/search/{term}", name="search", requirements={"term"="[a-zA-Z0-9]+([a-zA-Z0-9]*)"})
     */
    public function search(string $term)
    {
        return $this->render('search/index.html.twig', [
            'title' => 'Affichage du mot ' . $term,
            'content' => $this->getHtmlContentFor($term),
        ]);
    }
}
