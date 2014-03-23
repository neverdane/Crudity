<?php

class Crudity_Registry {

    public static function store($id, $form) { 
        if(!isset($_SESSION["Crudity"])) {
            $_SESSION["Crudity"] = array();
        }
        $_SESSION["Crudity"] = serialize(array($id => $form));
    }
    
    public static function get($id) {
        $cruditySession = unserialize($_SESSION["Crudity"]);
        if(isset($cruditySession[$id])) {
            return $cruditySession[$id];
        }
        return null;
    }
    
}