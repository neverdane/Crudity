<?php
namespace Neverdane\Crudity\Validator;

use Neverdane\Crudity\Error;

abstract class ValidatorAbstract
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

    public function validate($input)
    {
        $result = $this->check($input);
        if (isset($result["success"])) {
            return $result;
        }
        return $this->reject();
    }

    protected function check($input)
    {
        return $this->reject();
    }

    protected function testRegex($regex, $input)
    {
        return preg_match($regex, $input) === 1;
    }
}