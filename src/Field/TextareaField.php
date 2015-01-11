<?php
namespace Neverdane\Crudity\Field;

class TextareaField extends AbstractField implements FieldInterface {

    public static function getIdentifiers() {
        return array(
            "tagName"       => "textarea"
        );
    }

}
