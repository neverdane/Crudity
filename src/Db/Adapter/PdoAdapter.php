<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Db\Adapter;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class PdoAdapter extends AbstractAdapter implements AdapterInterface
{

    /**
     * @var \PDO
     */
    protected $connection;

    public function createRow($table, $data)
    {
        $columns = $this->getColumnsSlug($data);
        $values = $this->getValuesSlug($data);

        $this->connection->beginTransaction();
        $this->connection->exec("INSERT INTO $table ($columns) VALUES ($values)");
        $lastInsertId = $this->connection->lastInsertId();
        $this->connection->commit();
        return $lastInsertId;
    }

    public function updateRow($table, $id, $data)
    {
        $this->connection->beginTransaction();
        $assignments = $this->getAssignments($data);
        $idColumn = $this->getPrimaryKey($table);
        $result = $this->connection->exec("UPDATE $table SET $assignments WHERE $idColumn=$id");
        $this->connection->commit();
        return $result;
    }

    public function deleteRow($table, $id)
    {
        $this->connection->beginTransaction();
        $idColumn = $this->getPrimaryKey($table);
        $result = $this->connection->exec("DELETE FROM $table WHERE $idColumn=$id");
        $this->connection->commit();
        return $result;
    }

    private function getColumnsSlug($data)
    {
        return join(', ', array_keys($data));
    }

    private function getValuesSlug($data)
    {
        $values = array();
        foreach ($data as $value) {
            $values[] = $this->formatValue($value);
        }
        return join(', ', $values);
    }

    private function getAssignments($data)
    {
        $assignments = array();
        foreach($data as $column => $value) {
            $value = $this->formatValue($value);
            $assignments[] = "$column=$value";
        }
        return $assignments;
    }

    private function formatValue($value)
    {
        switch(gettype($value)) {
            default:
                return "'$value'";
                break;
            case 'integer':
            case 'double':
                return $value;
                break;
            case 'NULL':
                return 'NULL';
                break;
        }
    }

    private function getPrimaryKey($table)
    {
        return 'id';
    }
}