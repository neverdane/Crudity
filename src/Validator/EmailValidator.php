<?php
namespace Neverdane\Crudity\Validator;

class EmailValidator extends AbstractValidator
{

    protected function check($input)
    {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return $this->accept();
        }
    }

}