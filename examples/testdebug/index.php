<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('xdebug.show_local_vars', 1); // Code compilé en opcodes avant cet appel.
set_time_limit(1);
/*
function foo($a){
    while($a > 0)
        $a++; // Boucle infinie, erreur ici donc Xdebug indique seulement cette variable.
}*/
$a = 25;
$b = "25";
$c = array(25);
$d = array('25');
$e = foo($a);
?>