<?php

namespace App\Functions;

/**
 * Gather definition and relation from a request to JDM server..
 */
class CodeCleaner
{
    /**
     * Get whole HTML content for request (code).
     * From $page, extracts "$code" (interesting part of the server's answer).
     * From $code, get definitions (by getDefs()) and "datas" (by getDatas()).
     *
     * param : $page : whole answer from JDM (with header and stuff).
     * return : an object with accessors : defs, nodeTypes, relationsType (types de relation), relations, entries (?).
     */
    public function cleanCode($page){
        $start = stripos($page,'<code>');
        $stop = stripos($page,'</code>');
        //echo "Start : " . $start . " | Stop : " . $stop . "<br>";
        if($start !== false && $stop !== false){
            $code = substr($page, $start+6, $stop-$start-7);
            $code = utf8_encode($code);
            //echo $code;
            $cleanData = new class{}; //Objet anonyme
            $cleanData->defs = $this->getDefs($code);
            //var_dump($cleanData->defs);
            $cleanData = $this->getDatas($code, $cleanData);
            return $cleanData;
        }else{
            //echo "code non trouver";
        }
    }

    /**
     * Spliting defs (a def is a string beginning par something like "1.") in
     * an array.
     * A def may have associated example(s). Thus, returns an associated
     * array() with array->def and array->examples.
     *
     * param : $code : content (def and relations) from JDM.
     * returns : an array filled with associative array (def and examples).
     */
    public function getDefs($code){
        $start = stripos($code,'<def>');
        $stop = stripos($code,'</def>');
        if($start !== false && $stop !== false){
            $defToParse = substr($code, $start+5, $stop-$start-7);
            // final array of definitions and examples associated
            $defs = array();
            // one of the listed definitions. Began par "number."
            $aDef = preg_split("/([0-9]+\.)/",$defToParse);
            for($i = 0 ; $i < count($aDef) ; $i++){
                // for each definitions
                if($i != 0) {
                    $defAndEx = preg_split("/<br \/>/",$aDef[$i]);
                    $def = array();
                    // get definition
                    $def["def"] = $defAndEx[0];
                    $def["examples"] = array();
                    // get examples if there is.
                    for($j = 1 ; $j < count($defAndEx) ; $j++) {
                        // add the example to def.
                        array_push($def["examples"],$defAndEx[$j]);
                    }
                    // add the def to defs.
                    array_push($defs, $def);
                }
            }
            return $defs;
        }
        return "";
    }

    /**
     * Parse data and extracts NodeType, Entries, RelationTypes and Relations.
     *
     * From doc :
     * - NodeType = nt;ntid;'ntname'
     * - Entry = e;eid;'name';type;w;'formated name'
     * - RelationType = rt;rtid;'trname';'trgpname';'rthelp'
     * - Relation = r;r_id;noeud depart;nœud arrive;type;poids
     * TODO : mandatory fiels
     *
     * param : $code : content (def and relations) from JDM.
     *
     * returns : an object with accessors : nodeTypes, relationsType, relations, entries.
     */
    public function getDatas($code, $result){
        // get content after definitions which are already traited
        $start = stripos($code,'</def>');
        if($start !== false){
            $datas = substr($code, $start+6);
            $result->nodeTypes = array();
            $result->entries = array();
            $result->relationTypes = array();
            $result->relations = array();

            // for each line
            foreach(preg_split("/\n|\r/",$datas) as $d){

                // if it is "not empty"
                if(strlen($d) >= 2) {
                    if(strpos($d, 'nt;') !== false && !$this->isNoise($d, 'nt')){

                        // si contient nt;, c'est un nodetype
                        $parsedNT = preg_split("/;/", $d);
                        $nTArray = array();
                        //$nTArray["nt"] = $parsedNT[0];
                        $nTArray["id"] = $parsedNT[1];
                        $nTArray["name"] = substr($parsedNT[2], 1, strlen($parsedNT[2])-2);
                        array_push($result->nodeTypes, $nTArray);

                    } else if (strpos($d, 'e;') !== false && !$this->isNoise($d, 'e')) {

                        // TODO check all mandatory fields are precised
                        // si contient e; c'est une entrée
                        $parsedE = preg_split("/;/", $d);
                        $eArray = array();
                        //$eArray["e"] = $parsedE[0];
                        $eArray["id"] = $parsedE[1];
                        $eArray["name"] = substr($parsedE[2], 1, strlen($parsedE[2])-2);
                        $eArray["type"] = $parsedE[3];
                        $eArray["w"] = $parsedE[4];
                        //$eArray["formattedname"] = $parsedE[5];
                        array_push($result->entries, $eArray);

                    } else if (strpos($d, 'rt;') !== false && !$this->isNoise($d, 'rt')) {

                        // si contient par rt; c'est une relation
                        $parsedRT = preg_split("/;/", $d);
                        $rTArray = array();
                        //$rTArray["rt"] = $parsedRT[0];
                        $rTArray["id"] = $parsedRT[1];
                        $rTArray["name"] = substr($parsedRT[2], 1, strlen($parsedRT[2])-2);
                        $rTArray["gpname"] = substr($parsedRT[3], 1, strlen($parsedRT[3])-2);
                        $rTArray["help"] = substr($parsedRT[4], 1, strlen($parsedRT[4])-2);
                        array_push($result->relationTypes,$rTArray);

                    } else if (strpos($d, 'r;') !== false && !$this->isNoise($d, 'r')) {

                        // si contient par rt; c'est une relation
                        $parsedR = preg_split("/;/", $d);
                        $rArray = array();
                        //$rArray["r"] = $parsedR[0];
                        $rArray["id"] = $parsedR[1];
                        $rArray["nodeIn"] = $parsedR[2];
                        $rArray["nodeOut"] = $parsedR[3];
                        $rArray["type"] = $parsedR[4];
                        $rArray["weight"] = $parsedR[5];
                        array_push($result->relations,$rArray);

                    } else {
                        // sinon, c'est du bruit.
                    }
                } // if
            } // for
            var_dump($result->relationTypes);
            return $result;
        }
        return "";
    }

    /**
     * Check that the given $data is something interesting and not one of
     * MLF's comment.
     * params :
     *   - $data : the line to check
     *   - $pattern : the nt or e code that allow us to check if data is good
     * returns : true if $data is noised, false otherwise.
     */
    public function isNoise($data, string $pattern) : bool {
        if(strpos($data, $pattern . ";" . $pattern)){
            // we have nt;ntid (or e;eid and so on..) thus data is a comment
            return true;
        }
        return false;
    }



    /**
     * Extracts ID of a term (entry).
     */
    function getTermId($page){
        $start = stripos($page,'<code>');
        $stop = stripos($page,'</code>');
        //echo "Start : " . $start . " | Stop : " . $stop . "<br>";
        if($start !== false && $stop !== false){
            $code = substr($page, $start+6, $stop-$start-7);
            $code = utf8_encode($code);
            //ID is in a part of code that looks like (eid=ID)...
            $extraction = preg_split("/\(eid=/",$code);
            $id = preg_split("/\)/", $extraction[1]);
            return $id;
        }else{
            //echo "code non trouver";
        }
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
                $relationNameAnsi = convertToAnsi($relationName);
                if(!in_array($relationName, $names)){
                    array_push($names, $relationName);
                    $relation["id"] = $relationName;
                    $relation["entries"] = array();
                    if(!isset($relations["id_".$relationNameAnsi])){
                        $relations["id_".$relationName] = [];
                    }
                    $relations["id_".$relationName] = $relation;
                }
                // ajoute l'entrée dans la bonne catégorie de relation

                $entry = array();
                $entry["nodeIn"] = $this->getEntryName($r["nodeIn"], $cleanCode->entries);
                $entry["nodeInId"] = $r["nodeIn"];
                $entry["nodeOut"] = $this->getEntryName($r["nodeOut"], $cleanCode->entries);
                $entry["nodeOutId"] = $r["nodeOut"];
                $entry["weight"] = $r["weight"];
                if(!isset($relations["id_".$relationNameAnsi]["entries"])){
                    $relations["id_".$relationNameAnsi]["entries"] = [];
                }
                array_push($relations["id_".$relationNameAnsi]["entries"], $entry);
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

    /**
     * Returns an array of entries, somewhat in relation with $term by a $relation.
     */
    function getEntriesForRelation($page){
        $start = stripos($page,'<div class="listing">');
        if($start !== false){
            $codeBeg = substr($page, $start);
            $code = utf8_encode($codeBeg);
            $terms = array(); //Objet anonyme
            //<article<div<a<III>>>
            foreach(preg_split("/<article>/",$code) as $d){
                $articleDiv = $d;
                $begA = strpos($articleDiv, "<a");
                $endA = strpos($articleDiv, "</a>");

                if($begA !== false && $endA !== false){
                    $a = substr($articleDiv, $begA, $endA);
                    $beg = strpos($a, ">")+1;
                    $end = strpos(substr($a,1), "<");
                    if($beg !== false && $end !== false){
                        $term = substr($a, $beg, ($end+1)-$beg);
                        array_push($terms, $term);
                    }
                }
            }
            return $terms;
        }else{
            //echo "code non trouver";
        }
    }

}
