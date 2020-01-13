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
     * TODO attention on a encore les définitions séparées par des exemples
     * par un <br>
     *
     * param : $code : content (def and relations) from JDM.
     * returns : an array filled with definitions.
     */
    public function getDefs($code){
        $start = stripos($code,'<def>');
        $stop = stripos($code,'</def>');
        if($start !== false && $stop !== false){
            $defs = substr($code, $start+5, $stop-$start-7);
            return preg_split("/([0-9]+\.)/",$defs);
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
                        $nTArray["nt"] = $parsedNT[0];
                        $nTArray["ntid"] = $parsedNT[1];
                        $nTArray["ntname"] = $parsedNT[2];
                        array_push($result->nodeTypes, $nTArray);
                    } else if (strpos($d, 'e;') !== false && !$this->isNoise($d, 'e')) {
                        // TODO check all mandatory fields are precised
                        // si contient e; c'est une entrée
                        $parsedE = preg_split("/;/", $d);
                        $eArray = array();
                        $eArray["e"] = $parsedE[0];
                        $eArray["eid"] = $parsedE[1];
                        $eArray["ename"] = $parsedE[2];
                        $eArray["type"] = $parsedE[3];
                        $eArray["w"] = $parsedE[4];
                        //$eArray["formattedname"] = $parsedE[5];
                        array_push($result->entries, $eArray);
                    } else if (strpos($d, 'rt;') !== false && !$this->isNoise($d, 'rt')) {
                        // si contient par rt; c'est une relation
                        $parsedRT = preg_split("/;/", $d);
                        $rTArray = array();
                        $rTArray["rt"] = $parsedRT[0];
                        $rTArray["rtid"] = $parsedRT[1];
                        $rTArray["rtname"] = $parsedRT[2];
                        $rTArray["rtgpname"] = $parsedRT[3];
                        $rTArray["rthelp"] = $parsedRT[4];
                        array_push($result->relationTypes,$rTArray);
                    } else if (strpos($d, 'r;') !== false && !$this->isNoise($d, 'r')) {
                        // si contient par rt; c'est une relation
                        $parsedR = preg_split("/;/", $d);
                        $rArray = array();
                        $rArray["rt"] = $parsedR[0];
                        $rArray["rtid"] = $parsedR[1];
                        $rArray["rtname"] = $parsedR[2];
                        $rArray["rtgpname"] = $parsedR[3];
                        $rArray["rthelp"] = $parsedR[4];
                        array_push($result->relations,$rArray);
                    } else {
                        // sinon, c'est du bruit.
                    }
                } // if
            } // for
            //var_dump($result->relations);
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
}
