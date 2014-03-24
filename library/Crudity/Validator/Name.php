<?php

class Crudity_Validator_Name extends Crudity_Validator_Abstract {
    
     protected static function _validate($input) {
        if(filter_var($input, FILTER_VALIDATE_EMAIL)){
            return self::_accept();
        }
    }
    
}