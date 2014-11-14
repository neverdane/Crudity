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
        $fieldsOccurences = $this->getFieldsOccurences();
        $this->fields = $this->createFieldsInstances($fieldsOccurences);
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
            //$fields[] = $this->getAdapter()->createFieldInstance($occurence);
        }
    }

    public function createFieldsInstances($occurences)
    {
        $fields = array();
        foreach ($occurences as $occurence) {
            $fields[] = $this->createFieldInstance($occurence);
        }
        return $fields;
    }

    public function createFieldInstance($occurence)
    {
        $field = null;
        $fieldType = $this->identifyFieldType($occurence);
        return $field;
    }

    private function identifyFieldType($occurence)
    {
        $occurenceTagName = $this->getAdapter()->getTagName($occurence);
        $handledFields = $this->fieldHandler->getHandledFields();
        foreach($handledFields as $handledField) {
            $isField = $handledField::identify($this->getAdapter(), $occurence);

        }
        /*if (in_array($occurenceTagName, $managedTagNames)) {
            switch ($occurenceTagName) {
                case self::FIELD_INPUT:
                    $occurenceType = $this->getAdapter()->getAttribute(
                        $occurence,
                        "type"
                    );
                    if (is_null($occurenceType)) {
                        $occurenceType = $this->getAdapter()->getAttribute(
                            $occurence,
                            self::$prefix . "-type"
                        );
                    }
                    switch ($occurenceType) {
                        case self::FIELD_INPUT_CHECKBOX:
                        case self::FIELD_INPUT_EMAIL:
                        case self::FIELD_INPUT_PHONE:
                            return $occurenceType;
                        default:
                            return self::FIELD_INPUT_TEXT;
                    }
            }
            return $occurenceTagName;
        }*/
        return null;
    }

    public function getFieldsOccurences()
    {
        $occurences = $this->getAdapter()->getFieldsOccurences(
            self::$managedTagNames
        );
        return $this->filterOccurences($occurences);
    }

    private function filterOccurences($occurences)
    {
        $filteredOccurences = array();
        foreach ($occurences as $occurence) {
            if ($this->getAdapter()->isTargetField($occurence)) {
                $filteredOccurences[] = $occurence;
            }
        }
        return $filteredOccurences;
    }
}
