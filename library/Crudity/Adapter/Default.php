<?php
require_once("Crudity/Crudity/Adapter/Abstract.php");

class Crudity_Adapter_Default extends Crudity_Adapter_Abstract {

    public function manageAutoload() {
        parent::manageAutoload();
        spl_autoload_register(array(get_class($this), 'load'));
    }

    public function manageSession() {
        if(!isset($_SESSION)) {
            session_start();
        }
    }
    
    public function load($className) {
        $splitClassName = explode("_", $className);
        if ($splitClassName[0] === "Crudity") {
            $fileName = array_pop($splitClassName);
            $path = join("/", $splitClassName);
            $finalPath = "Crudity/" . $path . "/" . $fileName . ".php";
            if (file_exists($finalPath)) {
                require_once($finalPath);
            }
        }
        return false;
    }
    
    public function getRequestParams() {
        return $_POST;
    }    

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
    
    public function render($partial) {
        include $partial;
    }

}
