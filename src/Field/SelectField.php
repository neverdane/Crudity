<?php
namespace Neverdane\Crudity\Field;

class SelectField extends AbstractField implements FieldInterface {


    public static function getIdentifiers() {
        return array(
            "tagName"   => "select"
        );
    }
		
}
