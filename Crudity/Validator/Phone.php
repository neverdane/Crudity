<?php

class Crudity_Validator_Phone extends Crudity_Validator_Abstract {

    protected static $_regex = "([0-9]+[ .-+()])*[0-9]+";

    protected static function _validate($input) {
        if (self::_testRegex(self::$_regex, $input) === true) {
            return self::_accept();
        }
    }

}
