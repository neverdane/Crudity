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

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Db
{
    private $adapter = null;
    private $connection = null;

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

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function createRow($table, $data)
    {
        return $this->adapter->createRow($table, $data);
    }

    public function updateRow($table, $id, $data)
    {
        return $this->adapter->updateRow($table, $id, $data);
    }

    public function deleteRow($table, $id)
    {
        return $this->adapter->deleteRow($table, $id);
    }
}
