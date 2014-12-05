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
abstract class AbstractAdapter
{

    /**
     * @var null
     */
    protected $connection = null;
    /**
     * @var null|array
     */
    protected $credentials = null;

    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        if(is_null($this->connection) && !is_null($this->credentials)) {
            $this->createConnection();
        }
        return $this->connection;
    }

    public function createConnection()
    {
        return $this->connection;
    }

    /**
     * @return null|array
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param null|array $credentials
     * @return $this
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
        return $this;
    }

}