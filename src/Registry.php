<?php
namespace Neverdane\Crudity;

class Registry {

    const NAMESPACE_CRUDITY = "Crudity";

    public static function store($id, $form) { 
        if(!isset($_SESSION[self::NAMESPACE_CRUDITY])) {
            $_SESSION[self::NAMESPACE_CRUDITY] = array();
        }
        $_SESSION[self::NAMESPACE_CRUDITY] = serialize(array($id => $form));
    }
    
    public static function get($id) {
        $cruditySession = unserialize($_SESSION[self::NAMESPACE_CRUDITY]);
        if(isset($cruditySession[$id])) {
            return $cruditySession[$id];
        }
        return null;
    }
    
}