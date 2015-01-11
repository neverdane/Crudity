<?php
namespace Neverdane\Crudity\Field;

use Neverdane\Crudity\Validator\PhoneValidator;

class PhoneField extends AbstractField implements FieldInterface {

    protected function initializeValidators()
    {
        $this->validators = array(
            new PhoneValidator()
        );
    }

    public static function getIdentifiers() {
        return array(
            "tagName"       => "input",
            "attributes"    => array(
                "type"  => "tel"
            )
        );
    }

}
