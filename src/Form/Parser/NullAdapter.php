<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Form\Parser;

use Neverdane\Crudity\Form\View;
use phpQuery;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class NullAdapter implements AdapterInterface
{

    public function setHtml($html)
    {
        return $this;
    }

    public function getHtml()
    {
        return '';
    }

    public function getFormId()
    {
        return '';
    }
    public function getFieldsOccurrences($tagNames = array())
    {
        return array();
    }

    public function isFieldRelevant($occurrence)
    {
        return true;
    }

    public function getTagName($occurrence)
    {
        return '';
    }

    public function getAttribute($occurrence, $attributeKey)
    {
        return '';
    }

    public function setAttribute($occurrence, $attributeKey, $attributeValue)
    {
        return $this;
    }

    public function removeAttribute($occurrence, $attributeKey)
    {
        return $this;
    }

    public function getFormOccurrence()
    {
        return null;
    }
}
