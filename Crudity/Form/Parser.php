<?php

class Crudity_Form_Parser {

    /**
     * All the Form elements that Crudity can handle
     * @var array
     */
    protected static $_managedElemTypes = array(
        "input",
        "select",
        "textarea"
    );

    /**
     * We parse the form phpQuery Element and return an array containing :
     *  ["id"]      => (string) The id attribute of the HTML Element
     *  ["fields"]  => (array)  All the fields
     * @param phpQueryObject $formElem
     *  The form to parse
     * @return array
     */
    public static function parse($formElem) {
        return array(
            "id"        => $formElem->attr("id"),
            "fields"    => self::_identifyFields($formElem)
        );
    }

    /**
     * Identify fields
     * @param $formElem
     * @return array
     */
    protected static function _identifyFields($formElem) {
        $fields = array();
        // We get all the Form Elements managed by Crudity
        foreach(self::$_managedElemTypes as $elemType) {
            // We store all fields in a flatten array (That's why we use array_merge and not [])
            $fields = array_merge($fields, self::_getFields($elemType, $formElem));
        }
        return $fields;
    }

    protected static function _getFields($type, $html) {
        $inputs = $html->find($type);
        $fields = array();
        foreach ($inputs as $input) {
            $pqInput = pq($input);
            if ($pqInput->attr("name") === Crudity_Application::$prefix . "_partial"
                || $pqInput->attr("type") === "submit"
                || $pqInput->attr("cr-name") === ""
                || is_null($pqInput->attr("cr-name"))) {
                continue;
            }
            $fieldType = "Crudity_Form_Field_" . ucfirst(self::_identifyField($pqInput, $type));
            $fields[] = new $fieldType($type, $pqInput);
        }
        return $fields;
    }
    
    public static function _identifyField($pqInput, $elemType = null) {
        if (in_array($elemType, self::$_managedElemTypes)) {
            if ($elemType === "input") {
                $type = $pqInput->attr("type");
                if (is_null($type)) {
                    $type = $pqInput->attr(Crudity_Application::$prefix . "-type");
                }
                switch ($type) {
                    case "email":
                    case "phone":
                    case "checkbox":
                        return $type;
                }
                return "text";
            }
            return $elemType;
        }
        return null;
    }

}
