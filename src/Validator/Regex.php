<?php
namespace Neverdane\Crudity\Validator;

class ValidatorRegex extends ValidatorAbstract
{

    protected function check($input, $regex)
    {
        if ($this->testRegex($regex, $input) === true) {
            return $this->accept();
        }
    }

}