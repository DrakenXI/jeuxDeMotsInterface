<?php

function getCodeFor($mot){
  $page = file_get_contents("http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&gotermrel=".$mot."&rel=");
  return $page;
}

?>
