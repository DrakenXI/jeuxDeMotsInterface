<?php

namespace App\Controller;

use App\Entity\UserPreferences;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Security\Core\Security;

use App\Functions\JDMRequest;
use function App\Functions\convertToAnsi;
use function App\Functions\getEntriesFromFile;

class SearchController extends AbstractController
{
    private $cache;

    private $cacheDuraction;
    private $username;

    public function __construct(Security $security)
    {
        $this->cache = new FilesystemAdapter();
        $this->cacheDuraction = 604800; //Une semaine 604800
        // initialize var username with username if user connected, else : empty string
        ($security->getUser())? $this->username = $security->getUser()->getUsername():$this->username = "";
    }

    /**
     * Return true if the User preference is alphabetical order.
     */
    private function isAlphaOrderPreferred(){
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $this->username]);
        if($user){
            // get user preferences.
            $preferences = $entityManager->getRepository(UserPreferences::class)->findOneBy( ['user_id' => $user]);
            return $preferences->isAlphaSelected();
        }
        return true;
    }


    private function getCpt(){
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $this->username]);
        if($user){
            // get user preferences.
            $preferences = $entityManager->getRepository(UserPreferences::class)->findOneBy( ['user_id' => $user]);
            return $preferences->getCpt();
        }
        return 5;
    }

    private function getPage($term){
        $nomCache = 'cache-page-exacte-'.convertToAnsi($term);
        $value = $this->cache->get($nomCache, function (ItemInterface $item) use ($term) {
            $item->expiresAfter($this->cacheDuraction);
            $request = new JDMRequest();
            $page = $request->getDataFor($term, $this->isAlphaOrderPreferred());
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
            $page = $request->getApproxFor($term, $this->isAlphaOrderPreferred());
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
            $page = $request->getContentRelationIn($relation, $term, $this->isAlphaOrderPreferred());
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
            $page = $request->getDataFor($term, $this->isAlphaOrderPreferred());
            return $page->relations["id_".convertToAnsi($relation)]["entries"];
        });

        return $this->render('search/entriesDisplay.html.twig', [
            'entries' => $value,
            'cpt' => $this->getCpt(),
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
            if(isset($value->relations["id_".convertToAnsi("raffinement sémantique")])){
                return $value->relations["id_".convertToAnsi("raffinement sémantique")];
            }
            return null;
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

    /**
     * @Route("/search-auto-complet-letter/{letter}", name="search-auto-complet-letter", requirements={"letter"="[^/]*"})
     */
    public function searchAutoCompletLetter(string $letter)
    {
        $result = json_encode(null);
        $fileName = "./autocompletlist/symbole_".$letter.".json";
        if(file_exists("$fileName")){
            $result = file_get_contents($fileName);
        }
        return new JsonResponse($result);
    }

    //utiliser pour créer les fichier json de l'autocomplettion
//    /**
//     * @Route("/parsing-entries-from-file", name="parsing-entries-from-file")
//     */
//    public function parseEntiresFromFile()
//    {
//        $value = getEntriesFromFile();
//        return $this->render('search/displayDebug.html.twig', [
//            'value' => $value,
//        ]);
//    }
}
