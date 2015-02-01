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

    /**
     * @param mixed $entity
     * @param array $data
     * @return mixed
     */
    public function createRow($entity, $data)
    {
        return $this->getAdapter()->createRow($entity, $data);
    }

    public function updateRow($table, $id, $data)
    {
        return $this->getAdapter()->updateRow($table, $id, $data);
    }

    public function deleteRow($table, $id)
    {
        return $this->getAdapter()->deleteRow($table, $id);
    }

    public function selectRow($table, $id)
    {
        return $this->getAdapter()->selectRow($table, $id);
    }

}
