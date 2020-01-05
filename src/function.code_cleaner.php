<?php

function codeCleaner($page){
  $start = stripos($page,'<code>');
  $stop = stripos($page,'</code>');
  echo "Start : " . $start . " | Stop : " . $stop . "<br>";
  if($start !== false && $stop !== false){
    $code = substr($page, $start+6, $stop-$start-7);
    $code = utf8_encode($code);
    //echo $code;
    $cleanData = new class{}; //Objet anonyme
    $cleanData->defs = getDefs($code);

    $cleanData->datas = getDatas($code);

    return $cleanData;
  }else{
    echo "code non trouver";
  }
}

function getDefs($code){
  $start = stripos($code,'<def>');
  $stop = stripos($code,'</def>');
  if($start !== false && $stop !== false){
    $defs = substr($code, $start+5, $stop-$start-7);
    return preg_split("<br>",$defs);
  }
  return "";
}

function getDatas($code){
  $start = stripos($code,'</def>');
  $result = array();
  if($start !== false){
    $datas = substr($code, $start+6);
    foreach(preg_split("/\n|\r/",$datas) as $d){
      array_push($result,(explode(";", $d)));
    }
    return $result;
  }
  return "";
}

?>
