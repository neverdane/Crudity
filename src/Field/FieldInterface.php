<?php
namespace Neverdane\Crudity\Field;

Interface FieldInterface
{
    public static function identify($parser, $occurrence);
    public static function getIdentifiers();
    public static function createFromOccurrence($parser, $occurrence);
}
