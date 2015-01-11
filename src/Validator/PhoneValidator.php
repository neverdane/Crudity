<?php
namespace Neverdane\Crudity\Validator;

class PhoneValidator extends RegexValidator
{
    protected $regex = '/^([0-9\(\)\/\+ \-]*)$/';
}
