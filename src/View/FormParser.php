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
use Neverdane\Crudity\Field\FieldHandler;
use Neverdane\Crudity\View\Adapter\AdapterInterface;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class FormParser
{

    const ADAPTER_DEFAULT = "PhpQueryAdapter";

    private $html = "";
    private $adapter = null;

    private $id = null;
    private $fields = null;
    private $occurrences = null;
    private $formattedHtml = null;

    public function __construct($html)
    {
        $this->fieldHandler = new FieldHandler();
        $this->html = $html;
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
            $this->setAdapter(new $defaultAdapterName());
        }
        return $this->adapter;
    }

    /**
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter($adapter)
    {
        $adapter->setHtml($this->html);
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Returns for each fields occurrence its Field instance
     * @param array $occurrences
     * @return array
     */
    private function createFieldsInstances($occurrences)
    {
        $fields = array();
        foreach ($occurrences as $occurrence) {
            $fields[] = $this->createFieldInstance($occurrence);
        }
        return $fields;
    }

    private function createFieldInstance($occurrence)
    {
        $field = null;
        // In order to create it, we first need to identify its type
        $fieldType = $this->identifyFieldType($occurrence);
        if (!is_null($fieldType)) {
            $field = $fieldType::createFromOccurrence($this, $occurrence);
        }
        return $field;
    }

    private function identifyFieldType($occurrence)
    {
        // We get all the fields classes handled by Crudity
        $handledFields = $this->fieldHandler->getHandledFields();
        /** @var FieldInterface $handledField */
        foreach ($handledFields as $handledField) {
            // Foreach field class, we check if the occurrence is this type of field
            $isField = $handledField::identify($this, $occurrence);
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
    private function getFieldsOccurrences()
    {
        if (is_null($this->occurrences)) {
            $occurrences = $this->getAdapter()->getFieldsOccurrences(FormView::$managedTagNames);
            // We remove the non eligible fields occurrences
            $this->occurrences = $this->filterOccurrences($occurrences);
        }
        return $this->occurrences;
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
            if ($this->getAdapter()->isFieldRelevant($occurrence)) {
                $filteredOccurrences[] = $occurrence;
            }
        }
        return $filteredOccurrences;
    }

    public function getId()
    {
        if (is_null($this->id)) {
            // We ask our adapter to get back the form id
            $this->id = $this->getAdapter()->getFormId();
        }
        return $this->id;
    }

    /**
     * Returns the Fields instances extracted from the set html
     * They are also stored in the instance
     * @return array
     */
    public function getFields()
    {
        if (is_null($this->fields)) {
            // We ask our adapter to get back all the fields in an array
            // Returned occurrences are highly strongly coupled to the current parser adapter
            // which is the only one that can work with them
            $fieldsOccurrences = $this->getFieldsOccurrences();
            // It's time to convert this occurrence to Crudity fields instances, let's do it
            $this->fields = $this->createFieldsInstances($fieldsOccurrences);
        }
        return $this->fields;
    }
    /**
     * Returns the cleaned html based on the set html
     * It is also stored in the instance
     * @return string
     */
    public function getFormattedHtml()
    {
        if (is_null($this->formattedHtml)) {
            // We ask our adapter to get back all the fields in an array
            // Returned occurrences are highly strongly coupled to the current parser adapter
            // which is the only one that can work with them
            $fieldsOccurrences = $this->getFieldsOccurrences();
            // It's time to convert this occurrence to Crudity fields instances, let's do it
            $this->formattedHtml = $this->formatHtml($fieldsOccurrences)->getAdapter()->getHtml();
        }
        return $this->formattedHtml;
    }

    /**
     * @param array $occurrences
     * @return $this
     */
    private function formatHtml($occurrences)
    {
        foreach ($occurrences as $occurrence) {
            $this->cleanUpOccurrence($occurrence);
        }
        return $this;
    }

    /**
     * @param mixed $occurrence
     * @return $this
     */
    private function cleanUpOccurrence($occurrence)
    {
        $parserAdapter = $this->getAdapter();
        // We remove the crudity  column attribute
        $parserAdapter->removeAttribute($occurrence, FormView::$prefix . "-column");
        $parserAdapter->removeAttribute($occurrence, FormView::$prefix . "-excluded");
        return $this;
    }
}