<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Form\Parser;

use Neverdane\Crudity\Exception\CrudityException;
use Neverdane\Crudity\Field\FieldHandler;
use Neverdane\Crudity\Field\FieldInterface;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Parser
{

    const ADAPTER_DEFAULT = "PhpQueryAdapter";


    const PREFIX_DEFAULT = "cr";

    const FIELD_INPUT = "input";
    const FIELD_SELECT = "select";
    const FIELD_TEXTAREA = "textarea";

    /**
     * All the Form elements that Crudity can handle
     * (Different from the handled fields which are more specific)
     * @var array
     */
    public static $managedTagNames = array(
        self::FIELD_INPUT,
        self::FIELD_SELECT,
        self::FIELD_TEXTAREA,
    );

    public static $prefix = self::PREFIX_DEFAULT;

    private $html = "";
    private $adapter = null;

    private $id = null;
    private $occurrences = null;
    private $formattedHtml = null;
    private $fieldHandler = null;
    private $entities = null;

    public static function setPrefix($prefix = self::PREFIX_DEFAULT)
    {
        self::$prefix = $prefix;
    }


    /**
     * @param string $html
     * @param FieldHandler $fieldHandler
     */
    public function __construct($html, $fieldHandler = null)
    {
        // The Field Handler only purpose is to set the fields that will be identified by the Form Parser
        $this->fieldHandler = (!is_null($fieldHandler)) ? $fieldHandler : new FieldHandler();
        $this->html = $html;
    }

    /**
     * Returns the currently set Adapter instance that will parse the HTML
     * If none has been set, it will return and set the default one
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        // If no adapter has been set, we set the default one
        if (is_null($this->adapter)) {
            // We construct the default adapter namespace from this class namespace
            $defaultAdapterName = __NAMESPACE__ . '\\' . self::ADAPTER_DEFAULT;
            $this->setAdapter(new $defaultAdapterName());
        }
        return $this->adapter;
    }

    /**
     * Sets the Adapter instance that will be used to parse the HTML
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter($adapter)
    {
        // We have to set the html on the adapter
        $adapter->setHtml($this->html);
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Returns for each given fields occurrence its Field instance
     * @param array $occurrences
     * @param null|string $defaultEntityName
     * @throws CrudityException
     * @return array
     */
    private function getEntitiesDataByOccurrences($occurrences, $defaultEntityName = null)
    {
        $entities = array();
        foreach ($occurrences as $occurrence) {
            $fieldParams = $this->getFieldParams($occurrence);
            $entityName = $fieldParams['params']['entityName'];
            unset($fieldParams['params']['entityName']);
            if (is_null($entityName)) {
                if (is_null($defaultEntityName)) {
                    throw new CrudityException('The default Entity name must be given.');
                }
                $entityName = $defaultEntityName;
            }
            if (!isset($entities[$entityName])) {
                $entities[$entityName] = array('fields' => array());
            }
            $entities[$entityName]['fields'][] = $fieldParams;
        }
        return $entities;
    }

    /**
     * Creates a Field instance according to the given occurrence
     * If no Field instance has been identified, returns null
     * @param mixed $occurrence
     * @return null|array
     */
    private function getFieldParams($occurrence)
    {
        $fieldParams = null;
        // In order to create it, we first need to identify its type
        $fieldType = $this->identifyFieldType($occurrence);
        if (!is_null($fieldType)) {
            /** @var FieldInterface $fieldType */
            $fieldParams = array(
                'params' => $fieldType::getParamsFromOccurrence($this, $occurrence),
                'type' => $fieldType
            );
        }
        return $fieldParams;
    }

    /**
     * Identifies the type of the Field according to the given occurrence
     * Returns the name of the identified class.
     * If no Field instance has been identified, returns null
     * @param mixed $occurrence
     * @return null | string
     */
    private function identifyFieldType($occurrence)
    {
        // We get all the fields classes that we want to handle
        $handledFields = $this->fieldHandler->getHandledFields();
        /** @var FieldInterface $handledField */
        foreach ($handledFields as $handledField) {
            // Foreach Field class, we check if the occurrence is this type of field
            $isField = $handledField::identify($this, $occurrence);
            // If the field is identified as one, we return its Field class
            if ($isField === true) {
                return $handledField;
            }
        }
        return null;
    }

    /**
     * Returns all potential fields occurrences from the form as an array
     * We consider the fields eligible as they are in the managed elements by Crudity by default
     * @return array
     */
    public function getFieldsOccurrences()
    {
        if (is_null($this->occurrences)) {
            $occurrences = $this->getAdapter()->getFieldsOccurrences(self::$managedTagNames);
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
            if ($this->isFieldRelevant($occurrence)) {
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
     * @param null|string $defaultEntityName
     * @throws CrudityException
     * @return array
     */
    public function getEntitiesData($defaultEntityName = null)
    {
        if (is_null($this->entities)) {
            // We ask our adapter to get back all the fields in an array
            // Returned occurrences are highly strongly coupled to the current parser adapter
            // which is the only one that can work with them
            $fieldsOccurrences = $this->getFieldsOccurrences();
            // It's time to convert this occurrence to Crudity fields instances, let's do it
            $this->entities = $this->getEntitiesDataByOccurrences($fieldsOccurrences, $defaultEntityName);
        }
        return $this->entities;
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
        $parserAdapter->removeAttribute($occurrence, self::$prefix . "-column");
        $parserAdapter->removeAttribute($occurrence, self::$prefix . "-entity");
        $parserAdapter->removeAttribute($occurrence, self::$prefix . "-excluded");
        return $this;
    }

    public function insertConfig($config)
    {
        $parserAdapter = $this->getAdapter();
        $parserAdapter->setAttribute($parserAdapter->getFormOccurrence(), "data-config", json_encode($config));
        return $this;
    }

    /**
     * @return FieldHandler|null
     */
    public function getFieldHandler()
    {
        return $this->fieldHandler;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param mixed $occurrence
     * @return bool
     */
    private function isFieldRelevant($occurrence)
    {
        // We test if the current field analyzed is a Crudity functional field in the aim to not manage them
        // We reject the submit buttons and the inputs that have cr-excluded attribute
        return ($this->getAdapter()->getAttribute($occurrence, 'type') === "submit"
            || is_null($this->getAdapter()->getAttribute($occurrence, self::$prefix . "-excluded"))
        );
    }

}
