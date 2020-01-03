<?php

require_once $_SERVER["DOCUMENT_ROOT"]."/src/lib/function/function.jdm_request.php";

include $_SERVER["DOCUMENT_ROOT"]."/partials/header.php";
//header content <body>
?>

<h1>Affichage du mot : panda</h1>

<?php
echo getCodeFor("panda");
?>

<?php
//footer content </body>
include $_SERVER["DOCUMENT_ROOT"]."/partials/footer.php";
?>
