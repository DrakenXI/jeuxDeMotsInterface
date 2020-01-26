<?php
namespace App\Functions;

function convertToAnsi($mot){
    return iconv("UTF-8", "Windows-1252", $mot);
}
