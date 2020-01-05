<?php

require_once $_SERVER["DOCUMENT_ROOT"]."/src/lib/function/function.cache.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/src/lib/function/function.code_cleaner.php";

function getCodeFor($mot){
  //$wordCache = getCacheByWord($mot);

  $wordCache = null;

  if(!is_null($wordCache)){ // si on n'a un cache on le prend
    echo "Cache found";
  }else{ //sinon on fait la requete et on netoye
    echo "No cache";
    $page = file_get_contents("http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel=".$mot."&rel=");

    $code = codeCleaner($page);

    $affichage = "<table><thead><tr><th>N°</th><th>Definition</th></tr></thead><tbody>";


    for($i = 1; $i < sizeof($code->defs); $i++){
      $affichage = $affichage . "<tr><td>" . $i . "</td><td>" . $code->defs[$i] . "</td></tr>";
    }
    $affichage = $affichage . "</tbody></table>";

    $nodeType = array(); //Tableau associatif
    $relationType = array();
    $entries = array();

    $relationType = 2; //type de relation 2: sortante 3:entrante

    for($i = 1; $i < sizeof($code->datas); $i++){

      if(sizeof($code->datas[$i]) >= 2){//Si c'est des data (supprime les \n)

        switch ($code->datas[$i][0]) {
          case 'nt': //node type
            $nodeType["id_".$code->datas[$i][1]] = $code->datas[$i];
            break;
          case 'rt': //relation type
            $relationType["id_".$code->datas[$i][1]] = $code->datas[$i];
            break;
          case 'e':// entries
            $name = $code->datas[$i][2];
            if(isset($code->datas[$i][5])){
              $code->datas[$i][5];
            }
            $affichage = $affichage . "<tr><td>" . $name . "</td><td>" . $nodeType["id_".$code->datas[$i][3]][2] . "</td><td>" . $code->datas[$i][4] . "</td></tr>";
            $entries["id_".$code->datas[$i][3]] = $name; //on l'ajout dans la liste des entreis
            break;
          case 'r':// relation
            $affichage = $affichage . "<tr><td>" . $entries["id_".$code->datas[$i][$relationType]] . "</td><td>" . $relation["id_".$code->datas[$i][4]][2] . "</td><td>" . $code->datas[$i][5] . "</td></tr>";
            break;
          default:
            if(strpos($code->datas[$i][0], "// les noeuds/termes (Entries) : e") !== false){//entrer
                $affichage = $affichage . "<table><thead><tr><th>Nom</th><th>Type de noeud</th><th>Poids</th></tr></thead><tbody>";
            }elseif(strpos($code->datas[$i][0], "// les relations sortantes : r") !== false){//relation sortant
                $affichage = $affichage . "</tbody></table><table><thead><tr><th>Nom relation sortante</th><th>Type de relation</th><th>Poids</th></tr></thead><tbody>";
            }elseif(strpos($code->datas[$i][0], "// les relations entrantes : r") !== false){//relation sortant
                $affichage = $affichage . "</tbody></table><table><thead><tr><th>Nom relation entrantre</th><th>Type de relation</th><th>Poids</th></tr></thead><tbody>";
                $relationType = 3;
            }
            break;
        }

      }

    }//end for

    $affichage = $affichage . "</tbody></table>";

    echo $affichage;

    echo "Node type";
    print_r($nodeType);

  }

}

?>
