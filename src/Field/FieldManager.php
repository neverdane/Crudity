<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Field;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class FieldManager
{
    private $fields = array();

    public function __construct($fields = array())
    {
        $this->setFields($fields);
    }

    public function addField($field)
    {
        $this->fields[] = $field;
        return $this;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function affectValues($values)
    {
        $fields = $this->getFields();
        /** @var FieldInterface $field */
        foreach ($fields as $field) {
            if (isset($values[$field->getName()])) {
                $field->setValue($values[$field->getName()]);
            }
        }
    }

}
