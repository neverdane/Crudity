<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\View;

use Neverdane\Crudity\View\FieldHandler;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class FormView
{
    const PREFIX_DEFAULT = "cr";

    const ADAPTER_DEFAULT = "PhpQueryAdapter";

    const FIELD_INPUT = "input";
    const FIELD_SELECT = "select";
    const FIELD_TEXTAREA = "textarea";

    const FIELD_INPUT_TEXT = "text";
    const FIELD_INPUT_EMAIL = "email";
    const FIELD_INPUT_PHONE = "phone";
    const FIELD_INPUT_CHECKBOX = "checkbox";

    /**
     * All the Form elements that Crudity can handle
     * (Different from the handled fields which are more specific)
     * @var array
     */
    protected static $managedTagNames = array(
        self::FIELD_INPUT,
        self::FIELD_SELECT,
        self::FIELD_TEXTAREA,
    );

    private $html = "";
    private $adapter = null;
    private $fields = array();
    public static $prefix = self::PREFIX_DEFAULT;
    protected $handledFields = array();

    public function __construct($html)
    {
        $this->fieldHandler = new FieldHandler();
        $this->html = $html;
    }

    private static function getHandledFields() {

    }

    public static function setPrefix($prefix = self::PREFIX_DEFAULT) {
        self::$prefix = $prefix;
    }

    /**
     * @return Adapter\AbstractAdapter
     */
    public function getAdapter()
    {
        if (is_null($this->adapter)) {
            $defaultAdapterName = __NAMESPACE__ . '\\Adapter\\' . self::ADAPTER_DEFAULT;
            $this->adapter = new $defaultAdapterName();
        }
        return $this->adapter;
    }

    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    public function parse()
    {
        $viewAdapter = $this->getAdapter()->setHtml($this->html);
        $formId = $viewAdapter->getFormId();
        $fieldsOccurrences = $this->getFieldsOccurrences();
        $this->fields = $this->createFieldsInstances($fieldsOccurrences);
        $this->prepareFields();
        return array(
            "id" => $formId,
            "fields" => array()
        );
    }

    public function prepareFields()
    {
        foreach ($this->fields as $field) {
            //TODO Clean up HTML for each field
            //$fields[] = $this->getAdapter()->createFieldInstance($occurrence);
        }
    }

    public function createFieldsInstances($occurrences)
    {
        $fields = array();
        foreach ($occurrences as $occurrence) {
            $fields[] = $this->createFieldInstance($occurrence);
        }
        return $fields;
    }

    public function createFieldInstance($occurrence)
    {
        $field = null;
        $fieldType = $this->identifyFieldType($occurrence);
        return $field;
    }

    private function identifyFieldType($occurrence)
    {
        $occurrenceTagName = $this->getAdapter()->getTagName($occurrence);
        $handledFields = $this->fieldHandler->getHandledFields();
        foreach($handledFields as $handledField) {
            $isField = $handledField::identify($this->getAdapter(), $occurrence);

        }
        /*if (in_array($occurrenceTagName, $managedTagNames)) {
            switch ($occurrenceTagName) {
                case self::FIELD_INPUT:
                    $occurrenceType = $this->getAdapter()->getAttribute(
                        $occurrence,
                        "type"
                    );
                    if (is_null($occurrenceType)) {
                        $occurrenceType = $this->getAdapter()->getAttribute(
                            $occurrence,
                            self::$prefix . "-type"
                        );
                    }
                    switch ($occurrenceType) {
                        case self::FIELD_INPUT_CHECKBOX:
                        case self::FIELD_INPUT_EMAIL:
                        case self::FIELD_INPUT_PHONE:
                            return $occurrenceType;
                        default:
                            return self::FIELD_INPUT_TEXT;
                    }
            }
            return $occurrenceTagName;
        }*/
        return null;
    }

    public function getFieldsOccurrences()
    {
        $occurrences = $this->getAdapter()->getFieldsOccurrences(
            self::$managedTagNames
        );
        return $this->filterOccurrences($occurrences);
    }

    private function filterOccurrences($occurrences)
    {
        $filteredOccurrences = array();
        foreach ($occurrences as $occurrence) {
            if ($this->getAdapter()->isTargetField($occurrence)) {
                $filteredOccurrences[] = $occurrence;
            }
        }
        return $filteredOccurrences;
    }
}
