<?php
namespace Neverdane\Crudity\Validator;

use Neverdane\Crudity\Error;

class EmailValidator extends AbstractValidator
{

    public function validate($input)
    {
        if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return $this->reject(Error::WRONG_FORMAT);
        }
        return $this->accept();
    }

}