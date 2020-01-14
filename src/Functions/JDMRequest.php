<?php

namespace App\Functions;

class JDMRequest
{

    private $cleaner;

    public function __construct()
    {
        $this->cleaner = new CodeCleaner();
    }

    function getDataFor($mot)
    {
        //$wordCache = getCacheByWord($mot);
        $wordCache = null;

        //sinon on fait la requete et on nettoie
        $page = file_get_contents("http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel=" . $mot . "&rel=");
        $cleanCode =  $this->cleaner->cleanCode($page);

        $response = new class{};
        $response->defs = $cleanCode->defs;
        $response->relations = $this->extractRelations($cleanCode);
        return $response;
    }

    /**
     * Transform objects with nodeTypes, entries, relations and relationTypes
     * into a associative array filled with relations.
     *
     * param : $cleanCode : object from CodeCleaner
     * returns : associative array of relations.
     */
    function extractRelations($cleanCode) {
        $relations = array();
        $names = array();
        // for each relation
        foreach($cleanCode->relations as &$r){
            if($this->isRelationValid($r["type"])){
                $relation = array();
                // si relation pas enregitrée, crée une entrée.
                $isInArray = false;
                $relationName = $this->getRelationName($r, $cleanCode->relationTypes);
                if(!in_array($relationName, $names)){
                    array_push($names, $relationName);
                    $relation["id"] = $relationName;
                    $relation["entries"] = array();
                    array_push($relations, $relation);
                }
                // ajoute l'entrée dans la bonne catégorie de relation
                foreach($relations as &$relationCategory){
                    if($relationName == $relationCategory["id"]){
                        $entry = array();
                        $entry["nodeIn"] = $this->getEntryName($r["nodeIn"], $cleanCode->entries);
                        $entry["nodeOut"] = $this->getEntryName($r["nodeOut"], $cleanCode->entries);
                        $entry["weight"] = $r["weight"];
                        array_push($relationCategory["entries"], $entry);
                    }
                }
            }
        }
        return $relations;
    }

    /**
     * Filter relations.
     * returns : false if relation is one of the non-wanted. True otherwise
     */
    function isRelationValid($code){
        $unvalidRelationsId = array(12, 18, 19, 29, 33, 36, 45, 46, 47, 48, 66, 118, 128, 200, 444, 555, 1000, 1001, 1002, 2001);
        return !in_array($code, $unvalidRelationsId);
    }

    /**
     * Returns the name of a Relation.
     * Relation JOIN RelationType on r.type = rt.id
     *
     * params :
     *  - $r : the Relation we are looking the name for
     *  - $ rts : list of RelationType
     * returns : the "grand public" name of a relation.
     */
    function getRelationName($r, $rts){
        foreach($rts as $rt){
            if($rt["id"] === $r["type"]) {
                return $rt["gpname"];
            }
        }
        return "";
    }

    /**
     * Returns the name of an Entry corresponding to an ID.
     * Type JOIN Entries on t.nodeIn = e.id (or nodeOut).
     *
     * params :
     *  - nodeId : ID of an entry, either a "noeuddepart" or "noeudarrive" in Type.
     *  - entries : list of Entries
     * returns : the name of nodeId
     */
    function getEntryName($nodeId, $entries){
        foreach($entries as &$e){
            if($nodeId == $e["id"])
                return $e["name"];
        }
        return "";
    }
}
