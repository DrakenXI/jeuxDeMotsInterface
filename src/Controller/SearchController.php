<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $this->cacheDuraction = 5; //Une semaine 604800
    }

    private function getPreferences(){
        // fetch from DB
        $entityManager = $this->getDoctrine()->getManager();

        // fetch user
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $username]);
        if($user){
            // fetch user preferences.
            $preferences = $entityManager->getRepository(UserPreferences::class)->findOneBy( ['user_id' => $user]);
        }

        // set-up a UserPref object to render in form
        $prefs = new UserPreferences();
        if($preferences){
            // we have some preferences stored in DB for this user
            $isUpdate = true;
            $prefs->setMaxDisplay($preferences->getMaxDisplay());
            $prefs->setDisplayOrder($preferences->getDisplayOrder());
        } else {
            // we use default preferences
            $prefs->setMaxDisplay(20);
            $prefs->setDisplayOrder("alphabetique");
        }
        return $prefs;
    }

    private function getPage($term){
        $nomCache = 'cache-page-exacte-'.convertToAnsi($term);
        $value = $this->cache->get($nomCache, function (ItemInterface $item) use ($term) {
            $item->expiresAfter($this->cacheDuraction);
            $request = new JDMRequest();
            $page = $request->getDataFor($term);
            return $page;
        });

        return $value;
    }

    /**
     * @Route("/search/{term}", name="search", requirements={"term"="[^/]*"})
     */
    public function search(string $term)
    {

        $value = $this->getPage($term);

        if(is_null($value)){
            return $this->render('search/null.html.twig', [
                'title' => 'Résultat pour ' . $term,
                'term' => $term,
            ]);
        }else{
            return $this->render('search/index.html.twig', [
                'content' => $value,
                'term' => $term,
            ]);
        }
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

        if(is_null($value)){
            return $this->render('search/null.html.twig', [
                'title' => 'Résultat pour ' . $term,
                'term' => $term,
            ]);
        }else{
            return $this->render('search/indexApprox.html.twig', [
                'title' => 'Résultat pour ' . $term,
                'content' => $value,
                'term' => $term,
            ]);
        }
    }

    /**
     * @Route("/search-relations/{relation}/{term}", name="search-relations", requirements={"term"="[^/]*", "relation"="[0-9]+[0-9]*"})
     */
    public function searchRelations(string $term, string $relation)
    {
        $nomCache = 'cache-page-relation-'.convertToAnsi($term).'-'.convertToAnsi($relation);
        $value = $this->cache->get($nomCache, function (ItemInterface $item) use ($term, $relation) {
            $item->expiresAfter($this->cacheDuraction);
            $request = new JDMRequest();
            $page = $request->getContentRelationIn($relation, $term);
            // TODO correct encoding... encoding ok in var_dump
            //var_dump($page);
            return $page;
        });


        if(is_null($value)){
            return $this->render('search/null.html.twig', [
                'title' => 'Résultat pour ' . $term,
                'term' => $term,
            ]);
        }else{
            return $this->render('search/indexRelation.html.twig', [
                'title' => 'Résultat pour ' . $term,
                'term' => $term, /*TODO recup nom relation*/
                'relation' => $relation,
                'content' => $value,
            ]);
        }

    }

    /**
     * @Route("/search-entries-for-term-by-relation/{relation}/{term}", name="search-entries-for-term-by-relation", requirements={"term"="[^/]*","relation"="[^/]*"})
     */
    public function searchEntriesForTermByRelation(string $relation,string $term)
    {
        $nomCache = 'cache-page-exacte-entries-relation-'.convertToAnsi($term)."-".$relation;
        $value = $this->cache->get($nomCache, function (ItemInterface $item) use ($relation, $term) {
            $item->expiresAfter($this->cacheDuraction);
            $request = new JDMRequest();
            $page = $request->getDataFor($term);
            return $page->relations["id_".convertToAnsi($relation)]["entries"];
        });

        return $this->render('search/entriesDisplay.html.twig', [
            'entries' => $value,
        ]);
    }

    /**
     * @Route("/search-raffinement-list/{term}", name="search-raffinement-list", requirements={"term"="[^/]*"})
     */
    public function searchRaffinementList(string $term)
    {
        $nomCache = 'cache-raffinement-semantique-liste-'.convertToAnsi($term);

        $resultRaffine = $this->cache->get($nomCache, function (ItemInterface $item) use ($term) {
            $item->expiresAfter($this->cacheDuraction);
            $value = $this->getPage($term);
            return $value->relations["id_".convertToAnsi("raffinement sémantique")];
        });
        $result = null;
        if(!is_null($resultRaffine)){
            $result = $resultRaffine;
        }
        $result = json_encode($result);

        return new JsonResponse($result);
    }


    /**
     * @Route("/search-first-definition/{term}", name="search-first-definition", requirements={"term"="[^/]*"})
     */
    public function searchFirstDefinition(string $term)
    {
        $value = $this->getPage($term);
        $result = json_encode($value->defs);
        return new JsonResponse($result);
    }
}
