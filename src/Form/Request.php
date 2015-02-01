<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Form;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Request
{
    const ACTION_CUSTOM = "custom";
    const ACTION_CREATE = "create";
    const ACTION_READ = "read";
    const ACTION_UPDATE = "update";
    const ACTION_DELETE = "delete";
    const ACTION_FETCH = "fetch";

    private $action = null;
    private $rowId = null;
    private $params = array();
    private $rowIds = array();

    /**
     * @param null|array $requestParams
     */
    public function __construct($requestParams = null)
    {
        if (isset($requestParams["action"])) {
            $this->setAction($requestParams["action"]);
        }
        if (isset($requestParams["row_id"])) {
            $this->setRowId($requestParams["row_id"]);
        }
        if (isset($requestParams["params"])) {
            $this->setParams($requestParams["params"]);
        }
        if (isset($requestParams["row_ids"])) {
            $this->setRowIds($requestParams["row_ids"]);
        }
    }

    /**
     * Sets the action requested
     *
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Sets the rowId requested if any
     *
     * @param int $rowId
     * @return $this
     */
    public function setRowId($rowId)
    {
        $this->rowId = $rowId;
        return $this;
    }

    /**
     * Sets the sent params
     *
     * @param array $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Returns the action requested
     *
     * @return null|string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Returns the row id if any
     *
     * @return null|string
     */
    public function getRowId()
    {
        return $this->rowId;
    }

    /**
     * Returns the sent params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    public function getRowIds()
    {
        return $this->rowIds;
    }

    /**
     * @param array $rowIds
     * @return $this
     */
    public function setRowIds($rowIds)
    {
        $this->rowIds = $rowIds;
        return $this;
    }

}
