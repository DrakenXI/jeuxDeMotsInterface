<?php

namespace App\Functions;

class CodeCleaner
{
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

            $cleanData->datas = $this->getDatas($code);

            return $cleanData;
        }else{
            //echo "code non trouver";
        }
    }

    public function getDefs($code){
        $start = stripos($code,'<def>');
        $stop = stripos($code,'</def>');
        if($start !== false && $stop !== false){
            $defs = substr($code, $start+5, $stop-$start-7);
            return preg_split("<br>",$defs);
        }
        return "";
    }

    public function getDatas($code){
        $start = stripos($code,'</def>');
        $result = array();
        if($start !== false){
            $datas = substr($code, $start+6);
            foreach(preg_split("/\n|\r/",$datas) as $d){
                array_push($result,(str_getcsv($d ,";" , "'")));
            }
            return $result;
        }
        return "";
    }
}