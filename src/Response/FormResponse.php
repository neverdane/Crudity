<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Response;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class FormResponse
{
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;

    private $status = null;

    public function __construct()
    {
        $this->setStatus(self::STATUS_SUCCESS);
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function send()
    {
        echo json_encode(
            array(
                "status" => $this->status
            )
        );
        exit();
    }
}
