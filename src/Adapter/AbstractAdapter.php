<?php
namespace Neverdane\Crudity\Adapter;

abstract class AbstractAdapter {
    
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