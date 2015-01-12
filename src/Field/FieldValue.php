<?php
namespace Neverdane\Crudity\Field;

use Neverdane\Crudity\Error;
use Neverdane\Crudity\Validator\AbstractValidator;

class FieldValue
{
    const VALUE_REQUEST = "request";
    const VALUE_FILTERED = "filtered";

    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;

    private $values = array(
        self::VALUE_REQUEST => null,
        self::VALUE_FILTERED => null,
    );

    private $status = null;
    private $errorCode = null;
    private $errorValidatorName = null;

    public function __construct($value)
    {
        $this->setValue($value);
    }

    /**
     * Transforms the submitted value before being validated
     * Useful for int validations, checkboxes (submitted as "on" by jQuery...)
     * @param mixed $value
     *  The value to be transformed
     * @return mixed
     *  The value transformed
     */
    public function transform($value)
    {
        return $value;
    }


    public function getValue($type = null)
    {
        if (is_null($type)) {
            if (!is_null($this->values[self::VALUE_FILTERED])) {
                $type = self::VALUE_FILTERED;
            } elseif (!is_null($this->values[self::VALUE_REQUEST])) {
                $type = self::VALUE_REQUEST;
            }
        }
        return $this->values[$type];
    }

    public function validate($required = false, $validators = array())
    {
        $this->setStatus(self::STATUS_SUCCESS);
        $value = $this->getValue(self::VALUE_REQUEST);
        if ($required === true) {
            if (is_null($value) || $value === "") {
                $this->setStatus(self::STATUS_ERROR, Error::REQUIRED);
                return $this;
            }
        }
        /** @var AbstractValidator $validator */
        foreach ($validators as $validator) {
            $result = $validator->validate($value);
            if(false === $result['success']) {
                $this->setStatus(self::STATUS_ERROR);
                $this->setError($result["code"], $validator->getName());
                return $this;
            }
        }
        return $this;
    }

    public function filter($filters = array())
    {
        $this->setValue($this->getValue(self::VALUE_REQUEST), self::VALUE_FILTERED);
        foreach ($filters as $filter) {
            $value = $this->getValue(self::VALUE_FILTERED);
            $this->setValue($filter->filter($value), self::VALUE_FILTERED);
        }
        return $this;
    }

    private function setStatus($status, $error = null)
    {
        $this->status = $status;
        $this->setError($error);
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setError($code = null, $validatorName = null)
    {
        $this->errorCode = $code;
        $this->errorValidatorName = $validatorName;
        return $this;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorValidatorName()
    {
        return $this->errorValidatorName;
    }

    public function setValue($value, $type = self::VALUE_REQUEST)
    {
        $this->values[$type] = $value;
    }
}