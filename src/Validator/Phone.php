<?php
namespace Neverdane\Crudity\Validator;

class ValidatorPhone extends ValidatorAbstract {

    protected static $_regex = "([0-9]+[ .-+()])*[0-9]+";

    protected static function _validate($input) {
        if (self::_testRegex(self::$_regex, $input) === true) {
            return self::_accept();
        }
    }

}
