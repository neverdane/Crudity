<?php
namespace Neverdane\Crudity\Form\Field;

class CheckboxField extends AbstractField {

	public function transform($value) {
        //jQuery returns "on" on submitted checkbox, we want to convert it to 1 for database injection
		return ($value === "on") ? 1 : $value;
	}
	
}
