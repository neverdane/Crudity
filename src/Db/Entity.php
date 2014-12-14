<?php

namespace Neverdane\Crudity\Db;

class Entity
{

    private $name;
    private $entity;
    private $dependencies = null;
    private $fieldNames = null;
    private $defaultValues = array();

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
     * @param null|array $dependencies
     * @return $this
     */
    public function setDependencies($dependencies = null)
    {
        $this->dependencies = $dependencies;
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
        return $this->dependencies;
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
}