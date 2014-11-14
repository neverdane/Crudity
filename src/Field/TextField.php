<?php
namespace Neverdane\Crudity\Field;

class TextField extends AbstractField implements FieldInterface {

    public static function getIdentifiers() {
        return array(
            "tagName"       => "input",
            "attributes"    => array(
                "type"  => "text"
            )
        );
    }

}
