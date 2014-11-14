<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\View\Adapter;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class AbstractAdapter implements AdapterInterface
{
    protected $html = null;
    protected $formId = null;

    public function setHtml($html)
    {
        $this->html = $html;
        return $this;
    }

    public function getFormId()
    {
        return null;
    }

    public function getFieldsOccurrences($tagNames = array())
    {
        return array();
    }

    public function isTargetField($occurrence)
    {
        return true;
    }

    public function createFieldInstance($occurrence)
    {
        return null;
    }

    public function getTagName($occurrence)
    {
        return null;
    }

    public function getAttribute($occurrence, $attributeKey)
    {
        return null;
    }
}
