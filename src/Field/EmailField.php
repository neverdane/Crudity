<?php
namespace Neverdane\Crudity\Field;

class EmailField extends AbstractField {
    
    public $validators   = array("Email");

    public static function getIdentifiers() {
        return array(
            "tagName"       => "input",
            "attributes"    => array(
                "type"  => "email"
            )
        );
    }

}
