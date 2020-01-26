<?php

namespace App\Functions;

function getEntriesFromFile(){
    ini_set('memory_limit','2048M');
    set_time_limit ( 600 );

    $filePath = "./jdmtxts/01212020-LEXICALNET-JEUXDEMOTS-ENTRIES.txt";
    $entriesFiles = file_get_contents($filePath);
    $result  = array_map("str_getcsv", explode("\n", $entriesFiles));

    $entries = [];

    for($i = 3; $i < sizeof($result); $i++){
        $ex = explode(";",$result[$i][0]);
        if(isset($ex[0]) && isset($ex[1])){
            $econv = convertToAnsi($ex[1]);
            $firstLetter = strtolower(substr($econv, 0 ,1));
            if($firstLetter != "*" && $firstLetter != "?" && $firstLetter != "/" && $firstLetter != "\\" && $firstLetter != " "){
                $entries[$firstLetter][] = $econv;
            }
        }
    }

    foreach ($entries as $k => $v){
        $content = json_encode($v);
        file_put_contents("./autocompletlist/symbole_".$k.".json", $content);
        echo $k." | ";
    }


    return "Done !";
}
