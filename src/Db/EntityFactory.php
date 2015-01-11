<?php

namespace Neverdane\Crudity\Db;


use Neverdane\Crudity\Field\FieldFactory;

class EntityFactory
{

    /**
     * Creates and return an Entity
     * @param string $entityName
     * @param null|mixed $entity
     * @return Entity
     */
    public function createEntity($entityName, $entity = null)
    {
        return new Entity($entityName, $entity);
    }

    public function createEntityFromData($entityName, $entityData, $fieldFactory = null)
    {
        $fieldFactory = (!is_null($fieldFactory)) ? $fieldFactory : new FieldFactory();
        $entity = $this->createEntity($entityName, $entityData['entity']);
        $fields = array();
        foreach ($entityData['fields'] as $fieldData) {
            $field = $fieldFactory->createField($fieldData['type'], $fieldData['params']);
            $fields[$field->getName()] = $field;
        }
        $entity->setFields($fields);
        return $entity;
    }

    public function createEntities($entitiesData = array(), $fieldFactory = null){
        $entities = array();
        foreach ($entitiesData as $entityName => $entityData) {
            $entityData['entity'] = $entityName;
            $entity = $this->createEntityFromData($entityName, $entityData, $fieldFactory);
            $entities[$entity->getName()] = $entity;
        }
        return $entities;
    }

}