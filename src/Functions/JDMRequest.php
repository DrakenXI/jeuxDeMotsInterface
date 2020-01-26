<?php

namespace App\Functions;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class JDMRequest
{

    private $cacheDuraction;

    public function __construct()
    {
        $this->cleaner = new CodeCleaner();
        $this->cache = new FilesystemAdapter();
        $this->cacheDuraction = 5; //604800
    }

    /**
     * Main research mode
     */
    function getDataFor($term, $isAlphaOrdered)
    {
        //$wordCache = getCacheByWord($mot);
        $wordCache = null;

        $term = convertToAnsi($term);

        //sinon on fait la requete et on nettoie

        $termRe = str_replace(" ", "+", $term);
        $nomCache = 'cache-req-exacte-'.$termRe;
        $page = $this->cache->get($nomCache, function (ItemInterface $item) use ($termRe) {
            $item->expiresAfter($this->cacheDuraction);
            $page = file_get_contents("http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel=" . $termRe . "&rel=");
            return $page;
        });

        $cleanCode =  $this->cleaner->cleanCode($page);

        if(is_null($cleanCode)){
            return null;
        }

        $response = new class{};
        $response->defs = $cleanCode->defs;

        $nomCache = 'cache-extract-relation-'.$termRe;
        $retour = $this->cache->get($nomCache, function (ItemInterface $item) use ($cleanCode, $term) {
            $item->expiresAfter($this->cacheDuraction);
            $retour = $this->cleaner->extractRelations($cleanCode, $term);
            return $retour;
        });

        $response->relations = $retour;

        /*if($isAlphaOrdered){
        $orderedResponse = $response;
        foreach($orderedResponse->relations as $relation){
            $nodesIn = array();
            $nodesOut = array();
            foreach ($relation["entries"] as $key => $row)
            {
                $nodesIn[$key] = $row['nodeIn'];
                $nodesOut[$key] = $row['nodeOut'];
            }
            var_dump($nodesIn);
            var_dump($nodesOut);
            var_dump($relation["entries"]);
            array_multisort($nodesIn, SORT_ASC, $nodesOut, SORT_ASC, $relation["entries"]);
        }
        } else {*/
            $orderedResponse = new class{};
            $orderedResponse->defs = $response->defs;
            $orderedRelations = array();
            foreach($response->relations as $relation){
                $orderedRelation = array();
                $orderedRelation["id"] = $relation["id"];
                $weight = array();
                foreach ($relation["entries"] as $key => $row)
                {
                    $weight[$key] = $row['weight'];
                }
                array_multisort($weight, SORT_ASC,  $relation["entries"]);
                $orderedRelation["entries"]= $relation["entries"];
                var_dump($orderedRelation);
                array_push($orderedRelations, $orderedRelation);
            }
            $orderedResponse->relations = $orderedRelations;
        //}

        return $orderedResponse;
    }

    /**
     * Research all $terms that look like given $term.
     */
    function getApproxFor($term, $isAlphaOrdered){
        //$wordCache = getCacheByWord($mot);
        $wordCache = null;

        $term = convertToAnsi($term);

        $termRe = str_replace(" ", "+", $term);
        $nomCache = 'cache-req-approx-'.$term;
        $page = $this->cache->get($nomCache, function (ItemInterface $item) use ($termRe) {
            $item->expiresAfter($this->cacheDuraction);
            $page = file_get_contents("http://www.jeuxdemots.org/autocompletion/autocompletion.php?completionarg=proposition&proposition=".$termRe."t&trim=1");
            return $page;
        });

        $terms = array();
        foreach(preg_split("/ \* /",utf8_encode($page)) as $str){
            array_push($terms, $str);
        }
        $orderedResponse = $terms;
        foreach ($orderedResponse as $key => &$value) {
          $value = $this->_all_letters_to_ASCII($value);
        }
        return $orderedResponse;
    }

    /**
     * Research entries linked by $term with relation $relation.
     */
    function getContentRelationIn($relation, $term, $isAlphaOrdered){
        //$wordCache = getCacheByWord($mot);
        $wordCache = null;

        $term = convertToAnsi($term);

        $termRe = str_replace(" ", "+", $term);
        $nomCache = 'cache-req-termId-'.$term;
        $termId = $this->cache->get($nomCache, function (ItemInterface $item) use ($termRe) {
            $item->expiresAfter($this->cacheDuraction);
            $termId = $this->cleaner->getTermId(file_get_contents("http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel=" . $termRe . "&rel="))[0];
            return $termId;
        });
        $relation = convertToAnsi($relation);

        $nomCache = 'cache-req-relation-'.$termId.'-'.$relation;
        $page = $this->cache->get($nomCache, function (ItemInterface $item) use ($relation,$termId) {
            $item->expiresAfter($this->cacheDuraction);
            $page = file_get_contents("http://www.jeuxdemots.org/diko.php?select_relation_type=".$relation."&gotermrel_id=".$termId);
            return $page;
        });

        $nomCache = 'cache-clean-relation-'.$termId.'-'.$relation;
        $response = $this->cache->get($nomCache, function (ItemInterface $item) use ($page) {
            $item->expiresAfter($this->cacheDuraction);
            $response = $this->cleaner->getEntriesForRelation($page);
            return $response;
        });

        $orderedResponse = $response;
        foreach ($orderedResponse as $key => &$value) {
          $value = $this->_all_letters_to_ASCII($value);
        }
        return $orderedResponse;
    }

    /**
     * Function found on Stackoverflow-sama, helps with alphabetical order.
     */
    function _all_letters_to_ASCII($string) {
        return strtr(utf8_decode($string),
            utf8_decode('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'),
            'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
    }
}
