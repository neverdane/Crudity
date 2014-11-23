<?php
namespace Neverdane\Crudity\Validator;

class ValidatorEmail extends ValidatorAbstract
{

    protected function check($input)
    {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return $this->accept();
        }
    }

}