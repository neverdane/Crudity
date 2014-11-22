<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Request;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class FormRequest
{
    const ACTION_CUSTOM = "custom";

    private $action = null;
    private $rowId = null;
    private $params = null;

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
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setRowId($rowId)
    {
        $this->rowId = $rowId;
        return $this;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getRowId()
    {
        return $this->rowId;
    }

    public function getParams()
    {
        return $this->params;
    }

}
