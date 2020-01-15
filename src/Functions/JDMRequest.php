<?php

namespace App\Functions;

class JDMRequest
{

    private $cleaner;

    public function __construct()
    {
        $this->cleaner = new CodeCleaner();
    }

    /**
     * Main research mode
     */
    function getDataFor($mot)
    {
        //$wordCache = getCacheByWord($mot);
        $wordCache = null;

        //sinon on fait la requete et on nettoie
        $page = file_get_contents("http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel=" . $mot . "&rel=");

        $cleanCode =  $this->cleaner->cleanCode($page);

        $response = new class{};
        $response->defs = $cleanCode->defs;
        $response->relations = $this->cleaner->extractRelations($cleanCode);
        return $response;
    }

    /**
     * Research all $terms that look like given $term.
     */
    function getApproxFor($term){
        //$wordCache = getCacheByWord($mot);
        $wordCache = null;

        //sinon on fait la requete et on nettoie
        $page = file_get_contents("http://www.jeuxdemots.org/autocompletion/autocompletion.php?completionarg=proposition&proposition=".$term."t&trim=1");

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

        $termId = $this->cleaner->getTermId(file_get_contents("http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel=" . $term . "&rel="))[0];
        $page = file_get_contents("http://www.jeuxdemots.org/diko.php?select_relation_type=".$relation."&gotermrel_id=".$termId);
        $response = $this->cleaner->getEntriesForRelation($page);
        return $response;
    }
}
