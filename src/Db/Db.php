<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Db;

use Neverdane\Crudity\Db\Layer\AdapterInterface;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Db
{

    private static $adapters = array();
    private static $defaultAdapterKey = null;

    private $adapter = null;

    public static function setDefaultAdapterKey($key)
    {
        self::$defaultAdapterKey = $key;
    }

    public static function registerAdapter($key, $adapter, $default = false)
    {
        self::$adapters[$key] = $adapter;
        if (true === $default || 1 === count(self::$adapters)) {
            self::setDefaultAdapterKey($key);
        }
    }

    public static function retrieveAdapter($key = null)
    {
        if (isset(self::$adapters[$key])) {
            return self::$adapters[$key];
        } elseif (isset(self::$adapters[self::$defaultAdapterKey])) {
            return self::$adapters[self::$defaultAdapterKey];
        }
        return null;
    }

    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return null|AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    public function createRow($entity, $data, array $supportingEntities = array())
    {
        $entities = array_merge($supportingEntities, array($entity));
        $prioritizedEntities = $this->prioritizeEntities($entities);
        $entitiesIds = array();
        /** @var Entity $entity */
        foreach ($prioritizedEntities as $entity) {
            $dependencies = $entity->getDependencies();
            foreach ($dependencies as $column => $dependency) {

            }
            $entitiesIds[$entity->getName()][] = $this->getAdapter()->createRow($entity, $data);
        }
        foreach ($supportingEntities as $secondaryEntityData) {
            $supportingEntity = $secondaryEntityData['entity'];
            $fields = $secondaryEntityData['fields'];
            foreach ($fields as $field) {

            }
        }
        return $entitiesIds;
    }

    public function updateRow($table, $id, $data)
    {
        return $this->getAdapter()->updateRow($table, $id, $data);
    }

    public function deleteRow($table, $id)
    {
        return $this->getAdapter()->deleteRow($table, $id);
    }

    private function prioritizeEntities($entities)
    {
        $madeEntities = array();
        $sortedEntities = array();
        /** @var Entity $entity */
        while (count($entities) > 0) {
            foreach ($entities as $entityIndex => $entity) {
                $dependencies = $entity->getDependencies();
                $wait = false;
                if (!is_null($dependencies)) {
                    foreach ($dependencies as $dependency) {
                        if (!in_array($dependency, $madeEntities)) {
                            $wait = true;
                            break;
                        }
                    }
                }
                if (false === $wait) {
                    $madeEntities[] = $entity->getName();
                    $sortedEntities[] = $entity;
                    unset($entities[$entityIndex]);
                }
            }
        }
        return $sortedEntities;
    }

    /**
     * @param array $data
     * @param Entity $entity
     * @param array $supportingEntities
     */
    public function distributeDataOverEntities($data, $entity, $supportingEntities = array()) {
        $fieldsDistribution = array();
        /** @var Entity $supportingEntity */
        foreach ($supportingEntities as $supportingEntity) {
            $entityName = $supportingEntity->getName();
            $fieldsDistribution[$entityName] = array();
            $fields = $supportingEntity->getFieldNames();
            foreach ($fields as $field => $value) {
                if(isset($data[$field])) {
                    $fieldsDistribution[$entityName][$field] = $data[$field];
                    unset($data[$field]);
                }
                else {
                    $fieldsDistribution[$entityName][$field] = $supportingEntity->getDefaultValue($field);
                }
            }
        }
        return $fieldsDistribution;
    }
}
