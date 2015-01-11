<?php
namespace Neverdane\Crudity\Validator;

use Neverdane\Crudity\Error;

abstract class AbstractValidator
{

    public function getName()
    {
        return substr(get_class($this), strlen("Validator"));
    }

    protected function reject($code = Error::WRONG_FORMAT)
    {
        return array(
            "success" => false,
            "code" => $code
        );
    }

    protected function accept()
    {
        return array(
            "success" => true,
            "code" => null
        );
    }

    abstract public function validate($input);
}