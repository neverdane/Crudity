<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Db\Layer;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
interface AdapterInterface
{

    public function setConnection($connection);

    public function getConnection();

    public function createRow($entity, $data);

    public function updateRow($entity, $id, $data);

    public function deleteRow($entity, $id);

    public function selectRow($table, $id, $columns = array());
}