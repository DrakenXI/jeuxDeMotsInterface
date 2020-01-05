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
     * @Route("/search/{term}", name="search", requirements={"term"="[a-zA-Z0-9]+[a-zA-Z0-9]*"})
     */
    public function search(string $term)
    {
        return $this->render('search/index.html.twig', [
            'title' => 'Affichage du mot ' . $term,
            'content' => $this->getHtmlContentFor($term),
        ]);
    }

    /**
     * @Route("/search-approx/{term}", name="search-approx", requirements={"term"="[a-zA-Z0-9]+[a-zA-Z0-9]*"})
     */
    public function searchApprox(string $term)
    {
        return $this->render('search/index.html.twig', [
            'title' => 'Affichage du mot ' . $term,
            'content' => $this->getHtmlContentFor($term),
        ]);
    }

    /**
     * @Route("/search-relations/{term}/{relations}", name="search-relations", requirements={"term"="[a-zA-Z0-9]+[a-zA-Z0-9]*", "relations"="[a-zA-Z0-9]+[a-zA-Z0-9]*&[a-zA-Z0-9]+[a-zA-Z0-9]*"})
     */
    public function searchRelations(string $term, string $relations)
    {
        return $this->render('search/index.html.twig', [
            'title' => 'Affichage du mot ' . $term,
            'content' => $this->getHtmlContentFor($term),
        ]);
    }

    /**
     * @Route("/search-string/{term}", name="search-string", requirements={"term"="[a-zA-Z0-9]+[a-zA-Z0-9]*"})
     */
    public function searchString(string $term)
    {
        return $this->render('search/index.html.twig', [
            'title' => 'Affichage du mot ' . $term,
            'content' => $this->getHtmlContentFor($term),
        ]);
    }
}
