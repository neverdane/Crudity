<?php

abstract class Crudity_Adapter_Abstract {
    
    public function manageAutoload() {  
    }

    public function manageSession() {
    }
    
    public function getRequestParams() {
    }

    public static function store($id, $form) { 
    }
    
    public static function get($id) {
    }

    public function render($partial) {
    }
}