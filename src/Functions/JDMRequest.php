<?php

namespace App\Functions;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class JDMRequest
{

    private $cleaner;
    private $cache;

    private $cacheDuraction;

    public function __construct()
    {
        $this->cleaner = new CodeCleaner();
        $this->cache = new FilesystemAdapter();
        $this->cacheDuraction = 604800;
    }

    /**
     * Main research mode
     */
    function getDataFor($term)
    {
        //$wordCache = getCacheByWord($mot);
        $wordCache = null;

        $term = convertToAnsi($term);

        //sinon on fait la requete et on nettoie

        $nomCache = 'cache-req-exacte-'.$term;
        $page = $this->cache->get($nomCache, function (ItemInterface $item) use ($term) {
            $item->expiresAfter($this->cacheDuraction);
            $page = file_get_contents("http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel=" . $term . "&rel=");
            return $page;
        });

        $cleanCode =  $this->cleaner->cleanCode($page);

        $response = new class{};
        $response->defs = $cleanCode->defs;

        $nomCache = 'cache-extract-relation-'.$term;
        $retour = $this->cache->get($nomCache, function (ItemInterface $item) use ($cleanCode) {
            $item->expiresAfter($this->cacheDuraction);
            $retour = $this->cleaner->extractRelations($cleanCode);
            return $retour;
        });

        $response->relations = $retour;
        return $response;
    }

    /**
     * Research all $terms that look like given $term.
     */
    function getApproxFor($term){
        //$wordCache = getCacheByWord($mot);
        $wordCache = null;

        $nomCache = 'cache-req-approx-'.$term;
        $page = $this->cache->get($nomCache, function (ItemInterface $item) use ($term) {
            $item->expiresAfter($this->cacheDuraction);
            $page = file_get_contents("http://www.jeuxdemots.org/autocompletion/autocompletion.php?completionarg=proposition&proposition=".convertToAnsi($term)."t&trim=1");
            return $page;
        });

        $terms = array();
        foreach(preg_split("/ \* /",utf8_encode($page)) as $str){
            array_push($terms, $str);
        }
        return $terms;
    }

    /**
     * Research entries linked by $term with relation $relation.
     */
    function getContentRelationIn($relation, $term){
        //$wordCache = getCacheByWord($mot);
        $wordCache = null;

        $term = convertToAnsi($term);

        $nomCache = 'cache-req-termId-'.$term;
        $termId = $this->cache->get($nomCache, function (ItemInterface $item) use ($term) {
            $item->expiresAfter($this->cacheDuraction);
            $termId = $this->cleaner->getTermId(file_get_contents("http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel=" . $term . "&rel="))[0];
            return $termId;
        });

        $nomCache = 'cache-req-relation-'.$termId.'-'.$relation;
        $page = $this->cache->get($nomCache, function (ItemInterface $item) use ($relation,$termId) {
            $item->expiresAfter($this->cacheDuraction);
            $page = file_get_contents("http://www.jeuxdemots.org/diko.php?select_relation_type=".$relation."&gotermrel_id=".$termId);
            return $page;
        });


        $nomCache = 'cache-clean-relation-'.$termId.'-'.$relation;
        $page = $this->cache->get($nomCache, function (ItemInterface $item) use ($relation,$termId) {
            $item->expiresAfter($this->cacheDuraction);
            $page = file_get_contents("http://www.jeuxdemots.org/diko.php?select_relation_type=".$relation."&gotermrel_id=".$termId);
            return $page;
        });

        $response = $this->cleaner->getEntriesForRelation($page);
        return $response;
    }
}
