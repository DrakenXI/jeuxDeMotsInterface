<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use App\Functions\JDMRequest;
use function App\Functions\convertToAnsi;

class SearchController extends AbstractController
{
    private $cache;
    
    private $cacheDuraction;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
        $this->cacheDuraction = 604800; //Une semaine
    }

    /**
     * @Route("/search/{term}", name="search", requirements={"term"="[^/]*"})
     */
    public function search(string $term)
    {
        $nomCache = 'cache-page-exacte-'.convertToAnsi($term);
        $value = $this->cache->get($nomCache, function (ItemInterface $item) use ($term) {
            $item->expiresAfter($this->cacheDuraction);
            $request = new JDMRequest();
            $page = $request->getDataFor($term);
            return $page;
        });

        return $this->render('search/index.html.twig', [
            'title' => 'Résultat pour ' . $term,
            'content' => $value,
            'term' => $term,
        ]);
    }

    /**
     * @Route("/search-approx/{term}", name="search-approx", requirements={"term"="[^/]*"})
     */
    public function searchApprox(string $term)
    {
        $nomCache = 'cache-page-approx-'.convertToAnsi($term);
        $value = $this->cache->get($nomCache, function (ItemInterface $item) use ($term) {
            $item->expiresAfter($this->cacheDuraction);
            $request = new JDMRequest();
            $page = $request->getApproxFor($term);
            return $page;
        });
        return $this->render('search/indexApprox.html.twig', [
            'title' => 'Entrées essemblant à ' . $term,
            'term' => $term,
            'content' => $value,
            'term' => $term,
        ]);
    }

    /**
     * @Route("/search-relations/{relation}/{term}", name="search-relations", requirements={"term"="[^/]*", "relation"="[0-9]+[0-9]*"})
     */
    public function searchRelations(string $term, string $relation)
    {
        $nomCache = 'cache-page-relation-'.convertToAnsi($term).'-'.$relation;
        $value = $this->cache->get($nomCache, function (ItemInterface $item) use ($term, $relation) {
            $item->expiresAfter($this->cacheDuraction);
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
     * @Route("/search-string/{term}", name="search-string", requirements={"term"="[^/]*"})
     */
    public function searchString(string $term)
    {
        return $this->render('search/index.html.twig', [
            'title' => 'Affichage du mot ' . $term,
            'content' => $this->getHtmlContentFor($term),
            'term' => $term,
        ]);
    }

    /**
     * @Route("/search-entries-for-term-by-relation/{relation}/{term}/", name="search-entries-for-term-by-relation", requirements={"term"="[^/]*"})
     */
    public function searchEntriesForTermByRelation(string $relation,string $term)
    {
        $nomCache = 'cache-page-exacte-entries-relation-'.convertToAnsi($term)."-".$relation;
        $value = $this->cache->get($nomCache, function (ItemInterface $item) use ($relation, $term) {
            $item->expiresAfter($this->cacheDuraction);
            $request = new JDMRequest();
            $page = $request->getDataFor($term);
            return $page->relations[$relation];
        });

        return $this->render('search/entriesDisplay.html.twig', [
            'entries' => $value,
        ]);
    }
}
