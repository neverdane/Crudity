<?php
namespace Neverdane\Crudity\Validator;


use Neverdane\Crudity\Error;

abstract class ValidatorAbstract {

    protected static function _reject($code = Error::WRONG_FORMAT) {
        return array(
            "success"   => false,
            "code"      => $code
        );
    }
    
    protected static function _accept() {
        return array(
            "success"   => true,
            "code"      => null
        );
    }
    
    public static function validate($input) {
        $result = static::_validate($input);
        if(isset($result["success"])) {
            return $result;
        }
        return static::_reject();
    }
    
    protected static function _validate($input) {
        return self::_reject();
    }
    
    protected static function _testRegex($regex, $input) {
        return preg_match($regex, $input) === 1;
    }
}