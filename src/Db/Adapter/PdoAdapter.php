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
        $columns = join(', ', array_keys($data));

        $valuesTmp = array();
        foreach ($data as $value) {
            switch(gettype($value)) {
                default:
                    $valuesTmp[] = "'$value'";
                    break;
                case 'integer':
                case 'double':
                    $valuesTmp[] = $value;
                    break;
                case 'NULL':
                    $valuesTmp[] = 'NULL';
                    break;
            }
        }

        $values = join(', ', $valuesTmp);

        $this->connection->beginTransaction();
        $this->connection->exec("INSERT INTO $table ($columns) VALUES ($values)");
        $lastInsertId = $this->connection->lastInsertId();
        $this->connection->commit();
        return $lastInsertId;
    }

    public function updateRow($table, $id, $data)
    {

    }

    public function deleteRow($table, $id)
    {

    }
}