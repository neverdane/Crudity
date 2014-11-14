<?php
namespace Neverdane\Crudity\Field;

class PhoneField extends AbstractField implements FieldInterface {
    
    public $validator   = array("Phone");

    public static function getIdentifiers() {
        return array(
            "tagName"       => "input",
            "attributes"    => array(
                "type"  => "phone"
            )
        );
    }

}
