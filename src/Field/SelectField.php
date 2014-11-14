<?php
namespace Neverdane\Crudity\Field;

class SelectField extends AbstractField {


    public static function getIdentifiers() {
        return array(
            "tagName"   => "select"
        );
    }
		
}
