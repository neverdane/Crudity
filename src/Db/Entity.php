<?php

namespace Neverdane\Crudity\Db;

use Neverdane\Crudity\Error;
use Neverdane\Crudity\Field\AbstractField;
use Neverdane\Crudity\Field\FieldInterface;
use Neverdane\Crudity\Form\Response;

class Entity
{

    const DEFAULT_NAME = 'default';

    private $name;
    private $entity;
    private $dependencies = null;
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
     * @param null|array $dependencies
     * @return $this
     */
    public function setDependencies($dependencies = null)
    {
        $this->dependencies = $dependencies;
        foreach ($dependencies as $dependency) {

        }
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
     * @param Response $response
     * @param array $errorMessages
     * @return $this
     */
    public function validate($response, $errorMessages = array())
    {
        // We get all the fields we have to validate
        $fields = $this->getFields();
        foreach ($fields as $field) {
            // We validate each field and we get its status
            $fieldStatus = $field->validate()->getStatus();
            if ($fieldStatus !== AbstractField::STATUS_SUCCESS) {
                // If the validation fail, we set the response status to error
                $response->setStatus(Response::STATUS_ERROR);
                // We construct the error message that will be displayed to the user
                $message = Error::getMessage(
                    $field->getErrorCode(),
                    $errorMessages,
                    $field->getName(),
                    $field->getErrorValidatorName(),
                    $placeholders = array(
                        "value" => $field->getValue(),
                        "fieldName" => $field->getName()
                    )
                );
                // Then we add the error message for this field to the response
                $response->addError($field->getErrorCode(), $message, $field->getName());
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function filter()
    {   // We get all the fields we have to validate
        $fields = $this->getFields();
        foreach ($fields as $field) {
            $field->filter();
        }
        return $this;
    }

    /**
     * @param Db $db
     */
    public function create($db)
    {
        $values = $this->getValues();
        $rowIds = $db->createRow($this, $values);
        return $rowIds;
    }

    /**
     * @return array
     */
    private function getValues()
    {
        $data = array();
        $fields = $this->getFields();
        foreach ($fields as $fieldName => $field) {
            $data[$field->getName()] = $field->getValue();
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