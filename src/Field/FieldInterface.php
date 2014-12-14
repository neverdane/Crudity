<?php
namespace Neverdane\Crudity\Field;

Interface FieldInterface
{
    public static function identify($parser, $occurrence);

    public static function getIdentifiers();

    public static function getParamsFromOccurrence($parser, $occurrence);

    public function getName();

    /**
     * @return $this
     */
    public function validate();

    public function getStatus();

    public function getErrorCode();

    public function getErrorValidatorName();

    public function getValue();

    public function setValue($value, $type = null);
}
