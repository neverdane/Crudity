<?php

namespace Neverdane\Crudity\Db;

use Neverdane\Crudity\Error;
use Neverdane\Crudity\Field\FieldInterface;
use Neverdane\Crudity\Field\FieldValue;
use Neverdane\Crudity\Form\Response;

class Entity
{

    private $name;
    private $entity;
    private $fieldNames = null;
    private $defaultValues = array();
    private $fields = array();

    /**
     * @param string $entityName
     * @param null|mixed $entity
     */
    public function __construct($entityName, $entity = null)
    {
        $this->name = $entityName;
        $this->entity = $entity;
    }

    /**
     * @param $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @param null $fieldNames
     * @return $this
     */
    public function specifyFieldNames($fieldNames = null)
    {
        $this->fieldNames = $fieldNames;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|array
     */
    public function getDependencies()
    {
        $fields = $this->getFields();
        $dependencies = array();
        foreach ($fields as $fieldName => $field) {
            if (!is_null($field->getJoin())) {
                $join = $field->getJoin();
                if (!is_null($join)) {
                    $dependencies[] = $join;
                }
            }
        }
        return $dependencies;
    }

    /**
     * @return null|array
     */
    public function getFieldNames()
    {
        return $this->fieldNames;
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return $this->defaultValues;
    }

    /**
     * @param string $field
     * @return null|mixed
     */
    public function getDefaultValue($field)
    {
        return isset($this->defaultValues[$field])
            ? $this->defaultValues[$field]
            : null;
    }

    /**
     * @param array $defaultValues
     * @return $this
     */
    public function setDefaultValues($defaultValues = array())
    {
        $this->defaultValues = $defaultValues;
        return $this;
    }

    /**
     * @param FieldInterface[] $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param FieldInterface $field
     * @return $this
     */
    public function setField($field)
    {
        $this->fields[$field->getName()] = $field;
        return $this;
    }

    /**
     * @return FieldInterface[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Validates the value set on each Field and affects the Response
     *
     * @param Response $response
     * @param array $errorMessages
     * @return $this
     */
    public function validate($response, $errorMessages = array())
    {
        // We get all the fields we have to validate
        $fields = $this->getFields();
        foreach ($fields as $field) {
            $field->validate($response, $errorMessages);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function filter()
    {
        // We get all the fields we have to filter
        $fields = $this->getFields();
        foreach ($fields as $field) {
            $field->filter();
        }
        return $this;
    }

    /**
     * @param Db $db
     * @return array
     */
    public function create($db)
    {
        $rows = $this->getRowValues();
        $rowIds = array();
        foreach ($rows as $index => $rowValues) {
            $rowIds[$index] = $db->createRow($this->getEntity(), $rowValues);
        }
        return $rowIds;
    }

    /**
     * @param Db $db
     * @param int $id
     * @return array
     */
    public function fetch($db, $id)
    {
        return $db->selectRow($this->getEntity(), $id);
    }

    /**
     * @return array
     */
    private function getRowValues()
    {
        $data = array();
        $fields = $this->getFields();
        $rowCount = 0;
        foreach ($fields as $fieldName => $field) {
            $rowCount = count($field->getValues());
            break;
        }
        for ($i = 0; $i < $rowCount; $i++) {
            $data[$i] = array();
            foreach ($fields as $fieldName => $field) {
                $rowCount = count($field->getValues());
                $value = $field->getValue($i);
                $data[$i][$field->getName()] = $value->getValue();
            }
        }
        return $data;
    }

    /**
     * @return mixed|null
     */
    public function getEntity()
    {
        return $this->entity;
    }
}