<?php
namespace Neverdane\Crudity\Field;

use Neverdane\Crudity\Error;
use Neverdane\Crudity\Form\Parser\Parser;
use Neverdane\Crudity\Validator\ValidatorAbstract;

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

    private $values = array();
    private $join = null;

    public function __construct($config)
    {
        $this->name = $config["name"];
        if (!isset($config['column'])) {
            $this->column = $config["name"];
        } else {
            $this->column = $config["column"];
        }
        if (isset($config["required"])) {
            $this->required = $config["required"];
        }
        if (isset($config["join"])) {
            $this->join = $config["join"];
        }
        $this->initializeValidators();
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
     * In order to identify it, we should send the parser
     * which is highly coupled to the field occurrence
     * @param Parser $parser
     * @param mixed $occurrence
     * @return bool
     */
    public static function identify($parser, $occurrence)
    {
        // We firstly check the occurrence tag name
        if (static::checkTagName($parser, $occurrence)) {
            $ids = static::getIdentifiers();
            // Then we check each attribute
            if (isset($ids["attributes"]) && is_array($ids["attributes"])) {
                foreach ($ids["attributes"] as $key => $value) {
                    // If a parameter doesn't match, the occurrence is not this type of field class
                    if (!static::checkAttribute($parser, $occurrence, $key, $value)) {
                        return false;
                    }
                }
            }
            // If the tag name and the potential attributes are matching,
            // the occurrence is detected as this type of field class
            return true;
        }
        // If the tag name doesn't match, the occurrence is not this type of field class
        return false;
    }

    /**
     * Checks if the given field occurrence has the class tag name
     * @param Parser $parser
     * @param mixed $o
     * @return bool
     */
    private static function checkTagName($parser, $o)
    {
        // We get the identifiers of the class
        $ids = static::getIdentifiers();
        // We then compare the class tag name with the occurrence tag name
        return (isset($ids["tagName"])
            && $parser->getAdapter()->getTagName($o) === $ids["tagName"]);
    }

    /**
     * Checks if the given field occurrence has the given attribute
     * @param Parser $parser
     * @param mixed $o
     * @param string $key
     * @param string $value
     * @return bool
     */
    private static function checkAttribute($parser, $o, $key, $value)
    {
        // The type attribute could be prefixed by the Crudity prefix so we firstly check that one
        // We then compare the given attribute with the occurrence one
        if ($key === "type") {
            if ($parser->getAdapter()->getAttribute($o, Parser::$prefix . "-" . $key) === $value) {
                return true;
            }
        }
        return ($parser->getAdapter()->getAttribute($o, $key) === $value);
    }

    /**
     * @param Parser $parser
     * @param mixed $occurrence
     * @return static
     */
    public static function getParamsFromOccurrence($parser, $occurrence)
    {
        $parserAdapter = $parser->getAdapter();
        $required = $parserAdapter->getAttribute($occurrence, "required") === "true";
        $name = $parserAdapter->getAttribute($occurrence, "name");
        $column = $parserAdapter->getAttribute($occurrence, Parser::$prefix . "-column");
        $entityName = $parserAdapter->getAttribute($occurrence, Parser::$prefix . "-entity");
        $name = explode('[', $name)[0];
        if (is_null($column)) {
            // We use the Crudity name attribute
            $column = $name;
        }

        return array(
            "name" => $name,
            "column" => $column,
            "entityName" => $entityName,
            "required" => $required
        );
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value, $index = 0)
    {
        $this->values[$index] = $value;
    }

    public function getValue($index = 0)
    {
        return $this->values[$index];
    }

    public function getValues(){
        return $this->values;
    }

    public function validate()
    {
        foreach ($this->values as $value) {
            foreach ($this->validators as $validator) {
                $value->validate($validator);
            }
        }
        return $this;
    }

    public function filter()
    {
        foreach ($this->values as $value) {
            foreach ($this->filters as $filter) {
                $value->filter($filter);
            }
        }
        return $this;
    }

    /**
     * @return null|mixed
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return ValidatorAbstract[]
     */
    public function getValidators()
    {
        return $this->validators;
    }

    protected function initializeValidators()
    {
    }
}
