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

    const PARAMS_REQUEST = "request";
    const PARAMS_FILTERED = "filtered";

    private $action = null;
    private $rowId = null;
    private $params = array(
        self::PARAMS_REQUEST => null,
        self::PARAMS_FILTERED => null
    );

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

    public function setParams($params, $type = self::PARAMS_REQUEST)
    {
        $this->params[$type] = $params;
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

    public function getParams($type = null)
    {
        if (is_null($type)) {
            if (!is_null($this->params[self::PARAMS_FILTERED])) {
                $type = self::PARAMS_FILTERED;
            } elseif (!is_null($this->params[self::PARAMS_REQUEST])) {
                $type = self::PARAMS_REQUEST;
            }
        }
        return $this->params[$type];
    }

}
