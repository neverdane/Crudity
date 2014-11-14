<?php
namespace Neverdane\Crudity\Field;

class CheckboxField extends AbstractField {

	public function transform($value) {
        //jQuery returns "on" on submitted checkbox, we want to convert it to 1 for database injection
		return ($value === "on") ? 1 : $value;
	}

    public static function getIdentifiers() {
        return array(
            "tagName"       => "input",
            "attributes"    => array(
                "type"  => "checkbox"
            )
        );
    }

}
