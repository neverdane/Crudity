<?php
namespace Neverdane\Crudity\Field;

class InputField extends AbstractField implements FieldInterface {

    public static function getIdentifiers() {
        return array(
            "tagName"       => "input"
        );
    }

}
