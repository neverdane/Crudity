<?php
namespace Neverdane\Crudity\Validator;

class ValidatorPhone extends ValidatorAbstract
{

    protected $regex = "([0-9]+[ .-+()])*[0-9]+";

    protected function check($input)
    {
        if ($this->testRegex($this->regex, $input) === true) {
            return $this->accept();
        }
    }

}
