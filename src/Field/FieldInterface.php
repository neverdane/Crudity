<?php
namespace Neverdane\Crudity\Field;

Interface FieldInterface
{
    public static function identify($parser, $occurrence);

    public static function getIdentifiers();

    public static function getParamsFromOccurrence($parser, $occurrence);

    public function getName();

    /**
     * @param int $index
     * @return FieldValue
     */
    public function getValue($index = 0);

    public function setValue($value, $index = 0);

    /**
     * @return FieldValue[]
     */
    public function getValues();

    public function filter();

    public function getJoin();

    public function isRequired();

    public function getValidators();

    public function validate($response, $errorMessages = array());

    public function setDefaultValue($value);

    public function getDefaultValue();
}
