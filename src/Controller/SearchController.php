<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use App\Functions\JDMRequest;

class SearchController extends AbstractController
{
    private $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @Route("/search/{term}", name="search", requirements={"term"="[a-zA-Z0-9]+[a-zA-Z0-9]*"})
     */
    public function search(string $term)
    {
        $value = $this->cache->get('cache-'.$term, function (ItemInterface $item) use ($term) {
            $item->expiresAfter(10);
            $request = new JDMRequest();
            $page = $request->getDataFor($term);
            return $page;
        });
        return $this->render('search/index.html.twig', [
            'title' => 'Résultat pour ' . $term,
            'content' => $value,
        ]);
    }

    /**
     * @Route("/search-approx/{term}", name="search-approx", requirements={"term"="[a-zA-Z0-9]+[a-zA-Z0-9]*"})
     */
    public function searchApprox(string $term)
    {
        $value = $this->cache->get('cache-'.$term, function (ItemInterface $item) use ($term) {
            $item->expiresAfter(10);
            $request = new JDMRequest();
            $page = $request->getApproxFor($term);
            return $page;
        });
        return $this->render('search/indexApprox.html.twig', [
            'title' => 'Entrées essemblant à ' . $term,
            'term' => $term,
            'content' => $value,
        ]);
    }

    /**
     * @Route("/search-relations/{relation}/{term}", name="search-relations", requirements={"term"="[a-zA-Z0-9]+[a-zA-Z0-9]*", "relation"="[0-9]+[0-9]*"})
     */
    public function searchRelations(string $term, string $relation)
    {
        $value = $this->cache->get('cache-'.$term, function (ItemInterface $item) use ($term, $relation) {
            $item->expiresAfter(10);
            $request = new JDMRequest();
            $page = $request->getContentRelationIn($relation, $term);
            // TODO correct encoding... encoding ok in var_dump
            //var_dump($page);
            return $page;
        });
        return $this->render('search/indexRelation.html.twig', [
            'title' => 'Résultat pour ' . $term,
            'term' => $term, /*TODO recup nom relation*/
            'relation' => $relation,
            'content' => $value,
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
