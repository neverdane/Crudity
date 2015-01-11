<?php
namespace Neverdane\Crudity\Validator;

class DateValidator extends AbstractValidator
{

    protected $regex = '/^(\d{4})-(\d{2})-(\d{2})$/';

    protected function check($input)
    {
        if ($this->testRegex($this->regex, $input) === true) {
            return $this->accept();
        }
    }

}
