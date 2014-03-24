<?php

class Crudity_Validator_Regex extends Crudity_Validator_Abstract {
    
    protected static function _validate($input, $regex) {
        if(self::_testRegex($regex, $input) === true){ 
            return self::_accept();
        }
    }
    
}