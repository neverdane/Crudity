<?php
namespace Neverdane\Crudity\Field;

Interface FieldInterface
{
    public static function identify($view, $occurrence);
    public static function getIdentifiers();
}