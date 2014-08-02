<?php
namespace Neverdane\Crudity;

abstract class AbstractObserver
{

    public function __construct(&$form) {
        $this->form = $form;
    }
/*
    public function __call($method, $params)
    {
        if(method_exists($this, $method)) {
            $this->$method();
        }
        return;
    }
*/
    public function beforeValidation(){}
    public function afterValidation(){}
}
