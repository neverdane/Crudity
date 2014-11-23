<?php
namespace Neverdane\Crudity\Field;

use Neverdane\Crudity\Crudity;
use Neverdane\Crudity\Error;
use Neverdane\Crudity\View;

abstract class AbstractField implements FieldInterface
{
    const VALUE_REQUEST = "request";
    const VALUE_FILTERED = "filtered";

    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;

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

    private $value = array(
        self::VALUE_REQUEST => null,
        self::VALUE_FILTERED => null,
    );

    private $status = null;
    private $errorCode = null;
    private $errorValidatorName = null;

    public function __construct($config)
    {
        $this->name = $config["name"];
        $this->column = $config["column"];
        if (isset($config["required"])) {
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
     * In order to identify it, we should send the parser
     * which is highly coupled to the field occurrence
     * @param View\FormParser $parser
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
     * @param View\FormParser $parser
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
     * @param View\FormParser $parser
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
            if ($parser->getAdapter()->getAttribute($o, View\FormView::$prefix . "-" . $key) === $value) {
                return true;
            }
        }
        return ($parser->getAdapter()->getAttribute($o, $key) === $value);
    }

    /**
     * @param View\FormParser $parser
     * @param mixed $occurrence
     * @return static
     */
    public static function createFromOccurrence($parser, $occurrence)
    {
        // We extract the params from the occurrence
        $params = static::extractParamsFromOccurrence($parser, $occurrence);
        // Then we remove all the unneeded params from the occurrence
        static::cleanUpOccurrence($parser, $occurrence, $params);
        // Finally we return an instance of the field
        return new static($params);
    }

    /**
     * @param View\FormParser $parser
     * @param mixed $occurrence
     * @return array
     */
    public static function extractParamsFromOccurrence($parser, $occurrence)
    {
        $parserAdapter = $parser->getAdapter();
        $required = $parserAdapter->getAttribute($occurrence, "required") === "true";
        $name = $parserAdapter->getAttribute($occurrence, "name");
        $column = $parserAdapter->getAttribute($occurrence, View\FormView::$prefix . "-column");

        if (is_null($column)) {
            // We use the Crudity name attribute
            $column = $name;
        }

        return array(
            "name" => $name,
            "column" => $column,
            "required" => $required
        );
    }

    /**
     * @param View\FormParser $parser
     * @param mixed $occurrence
     * @return array
     */
    public static function cleanUpOccurrence($parser, $occurrence, $params = null)
    {
        $parserAdapter = $parser->getAdapter();
        // We remove the crudity name and column attributes
        $parserAdapter->removeAttribute($occurrence, View\FormView::$prefix . "-name");
        $parserAdapter->removeAttribute($occurrence, View\FormView::$prefix . "-column");
        if (is_null($params)) {
            // We need the params extracted from the occurrence to reset some attributes
            $params = static::extractParamsFromOccurrence($parser, $occurrence);
        }
        // We reset the name attribute
        $parserAdapter->setAttribute($occurrence, "name", $params["name"]);
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

    public function setValue($value, $type = self::VALUE_REQUEST)
    {
        $this->value[$type] = $value;
    }

    public function getValue($type = null)
    {
        if (is_null($type)) {
            if (!is_null($this->value[self::VALUE_FILTERED])) {
                $type = self::VALUE_FILTERED;
            } elseif (!is_null($this->value[self::VALUE_REQUEST])) {
                $type = self::VALUE_REQUEST;
            }
        }
        return $this->value[$type];
    }

    public function validate()
    {
        $this->setStatus(self::STATUS_SUCCESS);
        $value = $this->getValue(self::VALUE_REQUEST);
        if ($this->required === true) {
            if (is_null($value) || $value === "") {
                $this->setStatus(self::STATUS_ERROR, Error::REQUIRED);
                return $this;
            }
        }
        foreach ($this->validators as $validator) {
            $result = $validator->validate($value);
            $this->setStatus($result["status"]);
            if ($this->getStatus() === self::STATUS_ERROR) {
                $this->setError($result["code"], $validator->getName());
                return $this;
            }
        }
        return $this;
    }

    public function filter()
    {
        $this->setValue($this->getValue(self::VALUE_REQUEST), self::VALUE_FILTERED);
        foreach ($this->filters as $filter) {
            $value = $this->getValue(self::VALUE_FILTERED);
            $this->setValue($filter->filter($value), self::VALUE_FILTERED);
        }
        return $this;
    }

    private function setStatus($status, $error = null)
    {
        $this->status = $status;
        $this->setError($error);
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setError($code = null, $validatorName = null)
    {
        $this->errorCode = $code;
        $this->errorValidatorName = $validatorName;
        return $this;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorValidatorName()
    {
        return $this->errorValidatorName;
    }
}
