<?php
namespace Neverdane\Crudity\Field;

use Neverdane\Crudity\Crudity;
use Neverdane\Crudity\View;

abstract class AbstractField implements FieldInterface
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

    public function __construct($config)
    {
        $this->name = $config["name"];
        $this->column = $config["column"];
        if(isset($config["required"])) {
            $this->required = $config["required"];
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

    /**
     * Identifies if a field occurrence is eligible to be this type of class
     * In order to identify it, we should send the view
     * which is highly coupled to the field occurrence
     * @param View\FormView $view
     * @param mixed $occurrence
     * @return bool
     */
    public static function identify($view, $occurrence)
    {
        // We firstly check the occurrence tag name
        if (self::checkTagName($view, $occurrence)) {
            $ids = self::getIdentifiers();
            // Then we check each parameter
            if (isset($ids["parameters"]) && is_array($ids["parameters"])) {
                foreach ($ids["parameters"] as $key => $value) {
                    // If a parameter doesn't match, the occurrence is not this type of field class
                    if(!self::checkAttribute($view, $occurrence, $key, $value)) {
                        return false;
                    }
                }
            }
            // If the tag name and the potential parameters are matching,
            // the occurrence is detected as this type of field class
            return true;
        }
        // If the tag name doesn't match, the occurrence is not this type of field class
        return false;
    }

    /**
     * Checks if the given field occurrence has the class tag name
     * @param View\FormView $view
     * @param mixed $o
     * @return bool
     */
    private static function checkTagName($view, $o)
    {
        // We get the identifiers of the class
        $ids = self::getIdentifiers();
        // We then compare the class tag name with the occurrence tag name
        return (isset($ids["tagName"])
            && $view->getAdapter()->getTagName($o) === $ids["tagName"]);
    }

    /**
     * Checks if the given field occurrence has the given attribute
     * @param View\FormView $view
     * @param mixed $o
     * @param string $key
     * @param string $value
     * @return bool
     */
    private static function checkAttribute($view, $o, $key, $value)
    {
        // The type attribute could be prefixed by the Crudity prefix so we firstly check that one
        // We then compare the given attribute with the occurrence one
        if($key === "type") {
            if($view->getAdapter()->getAttribute($o, $view::$prefix . "-" . $key) === $value) {
                return true;
            }
        }
        return ($view->getAdapter()->getAttribute($o, $key) === $value);
    }

    /**
     * @param View\FormView $view
     * @param mixed $occurrence
     * @return static
     */
    public static function createFromOccurrence($view, $occurrence)
    {
        // We extract the params from the occurrence
        $params = self::extractParamsFromOccurrence($view, $occurrence);
        // Then we remove all the unneeded params from the occurrence
        self::cleanUpOccurrence($view, $occurrence);
        // Finally we return an instance of the field
        return new static($params);
    }

    /**
     * @param View\FormView $view
     * @param mixed $occurrence
     * @return array
     */
    public static function extractParamsFromOccurrence($view, $occurrence)
    {
        $viewAdapter = $view->getAdapter();
        $required = $viewAdapter->getAttribute($occurrence, "required") === "true";
        $name = $viewAdapter->getAttribute($occurrence, "name");
        $crudityName = $viewAdapter->getAttribute($occurrence, View\FormView::$prefix . "-name");
        $column = $viewAdapter->getAttribute($occurrence, View\FormView::$prefix . "-column");

        if (is_null($name) && !is_null($crudityName)) {
            // We use the Crudity name attribute
            $name = $crudityName;
        }
        if (is_null($column)) {
            // We use the Crudity name attribute
            $column = $name;
        }

        return array(
            "name"      => $name,
            "column"    => $column,
            "required"  => $required
        );
    }

    /**
     * @param View\FormView $view
     * @param mixed $occurrence
     * @return array
     */
    public static function cleanUpOccurrence($view, $occurrence, $params = null)
    {
        $viewAdapter = $view->getAdapter();
        // We remove the crudity name and column attributes
        $viewAdapter->removeAttribute($occurrence, View\FormView::$prefix . "-name");
        $viewAdapter->removeAttribute($occurrence, View\FormView::$prefix . "-column");
        if(!is_null($params)) {
            // We need the params extracted from the occurrence to reset some attributes
            $params = self::extractParamsFromOccurrence($view, $occurrence);
        }
        // We reset the name attribute
        $viewAdapter->setAttribute($occurrence, "name", $params["name"]);
    }
}
