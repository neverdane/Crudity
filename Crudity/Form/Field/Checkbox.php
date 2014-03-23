<?php

class Crudity_Form_Field_Checkbox extends Crudity_Form_Field_Abstract {

	public function transform($value) {
        //jQuery returns "on" on submitted checkbox, we want to convert it to 1 for database injection
		return ($value === "on") ? 1 : $value;
	}
	
}
