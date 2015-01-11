<?php
namespace Neverdane\Crudity\Validator;

use Neverdane\Crudity\Error;

class RegexValidator extends AbstractValidator
{
    protected $regex = '';

    public function validate($input)
    {
        if ($this->testRegex($input) !== true) {
            return $this->reject(Error::WRONG_FORMAT);
        }
        return $this->accept();
    }

    protected function testRegex($input)
    {
        return preg_match($this->regex, $input) === 1;
    }

}