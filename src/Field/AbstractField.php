<?php
namespace Neverdane\Crudity\Field;

use Neverdane\Crudity\Crudity;
use Neverdane\Crudity\View;

abstract class AbstractField
{
    /**
     * Sets if the field is required or not
     * On required, if the submitted value is empty, a message will be sent to the user
     * @var bool
     */
    public $required = false;
    /**
     * The name attribute of the HTML element
     * @var string
     */
    public $name = "";
    /**
     * The HTML element name of the HTML element (input, select...)
     * @var string
     */
    public $elem = "";
    /**
     * The corresponding column name in the database
     * This column name will be used for CRUD actions
     * @var string|null
     */
    public $column = null;
    /**
     * Validator names of Crudity_Validators to check
     * If validators are set and the user input does not match the required format, an error will be thrown to him
     * @var array
     */
    public $validators = array();
    /**
     * Filter names of Crudity_Filters to set
     * If filters are set, the user input, after being checked and validated if necessary, will be formatted as wanted
     * @var array
     */
    public $filters = array();
    /**
     * The type attribute of the HTML element
     * @var null|string
     */
    public $type = null;

    /**
     * Creates a Crudity_Field instance
     * Reads each attribute used by Crudity in order to set this instance
     * Cleans the attributes we want to hide to the final user (Crudity relative attributes)
     * @param $type
     *  The HTML element name of the HTML element (input, select...)
     * @param $field
     *  The DOM element
     */
    public function __construct($type, $field)
    {
        // We create a phpQueryObject instance in order to affect it
        $pqField = pq($field);
        $this->elem = $type;

        // We read all the attributes we need to set the Field instance
        $this->required = $field->attr("required") === "true";
        $this->name = $field->attr("name");
        $this->type = $field->attr("type");
        $this->column = $field->attr(Crudity::$prefix . "-column");
        $crudityName = $field->attr(Crudity::$prefix . "-name");
        $crudityType = $field->attr(Crudity::$prefix . "-type");

        // We remove the Crudity column param if defined to simplify the DOM and hide some settings to the final user
        if (!is_null($this->column)) {
            $field->removeAttr(Crudity::$prefix . "-column");
        }

        // We manage some fallback attributes, known as Crudity shortcuts
        if (!is_null($crudityName)) {
            // If the name attribute was not defined
            if (is_null($this->name)) {
                // We use the Crudity name attribute
                $this->name = $crudityName;
                // And we set the name attribute with this value
                $pqField->attr("name", $crudityName);
            }
            // If the Crudity column attribute was not defined
            if (is_null($this->column)) {
                // We use the Crudity name attribute
                $this->column = $crudityName;
            }
            // We remove the Crudity name attribute to simplify the DOM and hide some settings to the final user
            $field->removeAttr(Crudity::$prefix . "-name");
        }
        if (!is_null($crudityType)) {
            // If the name attribute was not defined
            if (is_null($this->type)) {
                // We use the Crudity type attribute
                $this->type = $crudityType;
                // And we set the type attribute with this value
                $pqField->attr("type", $crudityType);
            }
            // We remove the Crudity type attribute to simplify the DOM and hide some settings to the final user
            $field->removeAttr(Crudity::$prefix . "-type");
        }
    }

    /**
     * Transforms the submitted value before being validated
     * Useful for int validations, checkboxes (submitted as "on" by jQuery...)
     * @param mixed $value
     *  The value to be transformed
     * @return mixed
     *  The value transformed
     */
    public function transform($value)
    {
        return $value;
    }

    public static function getIdentifiers()
    {
        return array();
    }

    public static function identify($view, $occurence)
    {
        if (self::checkTagName($view, $occurence)) {
            if (isset($ids["parameters"]) && is_array($ids["parameters"])) {
                foreach ($ids["parameters"] as $key => $value) {
                    if(!self::checkAttribute($view, $occurence, $key, $value)) {
                        return false;
                    }
                }
                return true;
            }
            return true;
        }
        return false;
    }

    private static function checkTagName($view, $o)
    {
        $ids = self::getIdentifiers();
        return (isset($ids["tagName"])
            && $view->getAdapter()->getTagName($o) === $ids["tagName"]);
    }

    private static function checkAttribute($view, $o, $key, $value)
    {
        if($key === "type") {
            if($view->getAdapter()->getAttribute($o, $view::$prefix . "-" . $key) === $value) {
                return true;
            }
        }
        return ($view->getAdapter()->getAttribute($o, $key) === $value);
    }
}
