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
interface AdapterInterface
{

    public function setConnection($connection);

    public function getConnection();

    public function createRow($table, $data);

    public function updateRow($table, $id, $data);

    public function deleteRow($table, $id);
}