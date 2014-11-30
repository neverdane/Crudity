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

use Neverdane\Crudity\Db\Adapter\AdapterInterface;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Db
{
    private static $defaultAdapter = null;
    private static $defaultConnection = null;

    private $adapter = null;
    private $connection = null;

    public static function setDefaultAdapter($adapter)
    {
        self::$defaultAdapter = $adapter;
    }

    public static function getDefaultAdapter()
    {
        return self::$defaultAdapter;
    }

    public static function setDefaultConnection($connection)
    {
        self::$defaultConnection = $connection;
    }

    public static function getDefaultConnection()
    {
        return self::$defaultConnection;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }

    public function getConnection()
    {
        return $this->connection;
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

    public function createRow($table, $data)
    {
        return $this->getAdapter()->createRow($table, $data);
    }

    public function updateRow($table, $id, $data)
    {
        return $this->getAdapter()->updateRow($table, $id, $data);
    }

    public function deleteRow($table, $id)
    {
        return $this->getAdapter()->deleteRow($table, $id);
    }
}
