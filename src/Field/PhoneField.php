<?php
namespace Neverdane\Crudity\Field;

class PhoneField extends AbstractField {
    
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
