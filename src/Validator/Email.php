<?php
namespace Neverdane\Crudity\Validator;

class ValidatorEmail extends ValidatorAbstract {
    
    protected static function _validate($input) {
        if(filter_var($input, FILTER_VALIDATE_EMAIL)){ 
            return self::_accept();
        }
    }
    
}