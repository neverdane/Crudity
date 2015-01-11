<?php
namespace Neverdane\Crudity\Field;

use Neverdane\Crudity\Validator\DateValidator;

class DateField extends AbstractField implements FieldInterface {

    protected function initializeValidators()
    {
        $this->validators = array(
            new DateValidator()
        );
    }

    public static function getIdentifiers() {
        return array(
            "tagName"       => "input",
            "attributes"    => array(
                "type"  => "date"
            )
        );
    }

}
