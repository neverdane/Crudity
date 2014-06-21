<?php
namespace Neverdane\Crudity\Validator;

class ValidatorRegex extends ValidatorAbstract {
    
    protected static function _validate($input, $regex) {
        if(self::_testRegex($regex, $input) === true){ 
            return self::_accept();
        }
    }
    
}