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

use Neverdane\Crudity\Field\FieldInterface;
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

    private static function getHandledFields()
    {

    }

    public static function setPrefix($prefix = self::PREFIX_DEFAULT)
    {
        self::$prefix = $prefix;
    }

    /**
     * @return Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        // If no adapter has been set, we set the default one
        if (is_null($this->adapter)) {
            // We construct the default adapter namespace from this class namespace
            $defaultAdapterName = __NAMESPACE__ . '\\Adapter\\' . self::ADAPTER_DEFAULT;
            $this->adapter = new $defaultAdapterName();
        }
        return $this->adapter;
    }

    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Parses the view html and extracts its params as an array :
     *  "id"        => the form id,
     *  "fields"    => Crudity fields instances from the form
     * @return array
     */
    public function parse()
    {
        // We get the adapter that will parse our html
        $viewAdapter = $this->getAdapter();
        $viewAdapter->setHtml($this->html);
        // We ask our adapter to get back the form id
        $formId = $viewAdapter->getFormId();
        // We also ask our adapter to get back all the fields in an array
        // Returned occurrences are highly strongly coupled to the current view adapter
        // which is the only one that can work with them
        $fieldsOccurrences = $this->getFieldsOccurrences();
        // It's time to convert this occurrence to Crudity fields instances, let's do it
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

    /**
     * Returns for each fields occurrence its Field instance
     * @param array $occurrences
     * @return array
     */
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
        // In order to create it, we first need to identify its type
        $fieldType = $this->identifyFieldType($occurrence);
        return $field;
    }

    private function identifyFieldType($occurrence)
    {
        // We get all the fields classes handled by Crudity
        $handledFields = $this->fieldHandler->getHandledFields();
        /** @var FieldInterface $handledField */
        foreach ($handledFields as $handledField) {
            // Foreach field class, we check if the occurrence is this type of field
            $isField = $handledField::identify($this->getAdapter(), $occurrence);
            // If the field is identified as one, we return its Field class
            if ($isField === true) {
                return $handledField;
            }
        }
        return null;
    }

    /**
     * Returns all potential fields occurrences as an array from the form
     * We consider the fields eligible as they are in the managed elements by Crudity by default
     * @return array
     */
    public function getFieldsOccurrences()
    {
        $occurrences = $this->getAdapter()->getFieldsOccurrences(self::$managedTagNames);
        // We remove the non eligible fields occurrences
        return $this->filterOccurrences($occurrences);
    }

    /**
     * Removes the fields occurrences that could not be handled by Crudity (submit, ...)
     * @param array $occurrences
     * @return array
     */
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
