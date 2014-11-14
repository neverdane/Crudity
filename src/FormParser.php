<?php
namespace Neverdane\Crudity;

use phpQuery;

class FormParser
{
    const FIELD_INPUT = "input";
    const FIELD_SELECT = "select";
    const FIELD_TEXTAREA = "textarea";

    private static $instance = null;

    /**
     * All the Form elements that Crudity can handle
     * @var array
     */
    private static $managedElemTypes = array(
        self::FIELD_INPUT,
        self::FIELD_SELECT,
        self::FIELD_TEXTAREA,
    );

    private function __construct()
    {
    }

    public static function getInstance()
    {

        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function initDoc($html)
    {
    }

    /**
     * We parse the form phpQuery Element and return an array containing :
     *  ["id"]      => (string) The id attribute of the HTML Element
     *  ["fields"]  => (array)  All the fields
     * @param phpQueryObject $formElem
     *  The form to parse
     * @return array
     */
    public static function parseHtml($html)
    {
        $formElem = phpQuery::newDocument($html)->find("form");
        return array(
            "id" => "id",
            "fields" => array()
        );
        return array(
            "id" => $formElem->attr("id"),
            "fields" => self::_identifyFields($formElem)
        );
    }

    /**
     * Identify all fields managed by Crudity and return Crudity_Form_Field instances in an indexed array
     * @param phpQueryObject $formElem
     *  The form to parse
     * @return array
     *  Indexed array
     */
    protected static function _identifyFields($formElem)
    {
        $fields = array();
        // We get all the Form Elements managed by Crudity
        foreach (self::$_managedElemTypes as $elemType) {
            // We store all fields in a flatten array (That's why we use array_merge and not [])
            $fields = array_merge($fields, self::_getFields($elemType, $formElem));
        }
        return $fields;
    }

    /**
     * Return all Crudity_Form_Field instances of a specific type from $formElem
     * @param string $type
     *  The selector of the element (input, select, ...)
     * @param phpQueryObject $formElem
     *  The form to parse
     * @return array
     *  Indexed array
     */
    protected static function _getFields($type, $formElem)
    {
        // We get all elements from the DOM with selector $type
        $inputs = $formElem->find($type);
        $fields = array();
        // Foreach element, we create an Crudity_Form_Field instance of its type
        foreach ($inputs as $input) {
            // We transform the element as a phpQuery object
            $pqInput = pq($input);
            // We test if the current field analyzed is a Crudity functional field in the aim to not manage them
            if ($pqInput->attr("name") === Crudity::$prefix . "_partial"
                || $pqInput->attr("type") === "submit"
                || $pqInput->attr("cr-name") === ""
                || is_null($pqInput->attr("cr-name"))
            ) {
                continue;
            }
            // We factory the Crudity_Form_Field class name
            $fieldType = "Neverdane\\Crudity\\Form\\Field\\" . ucfirst(self::_identifyField($pqInput, $type)) . "Field";
            // We add the instance to the returned array
            $fields[] = new $fieldType($type, $pqInput);
        }
        return $fields;
    }

    /**
     * Identifies the Crudity_Form_Field type of the given $pqInput element
     * @param phpQueryObject $pqInput
     *  The element to analyze
     * @param null|string $elemType
     *  The selector of the element
     * @return null|string
     */
    public static function _identifyField($pqInput, $elemType = null)
    {
        if (in_array($elemType, self::$_managedElemTypes)) {
            if ($elemType === "input") {
                $type = $pqInput->attr("type");
                if (is_null($type)) {
                    $type = $pqInput->attr(Crudity::$prefix . "-type");
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
