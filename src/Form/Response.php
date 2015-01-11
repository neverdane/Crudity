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
class Response
{
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;

    private $status = null;
    private $errors = array(
        'fields' => array(),
        'global' => null
    );
    private $params = array();

    public function __construct()
    {
        $this->setStatus(self::STATUS_SUCCESS);
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function send()
    {
        echo json_encode(
            array(
                'status' => $this->status,
                'errors' => $this->errors,
                'params' => $this->params
            )
        );
        exit();
    }

    public function addError($code, $message, $fieldName = null, $index = 0)
    {
        $error = array(
            'code' => $code,
            'message' => $message
        );
        if (!is_null($fieldName)) {
            $this->errors['fields'][$fieldName][$index] = $error;
        } else {
            $this->errors['global'] = $error;
        }
        return $this;
    }

    public function addParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }
}
