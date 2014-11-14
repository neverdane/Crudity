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
interface AdapterInterface
{
    public function setHtml($html);
    public function getFormId();
    public function getFieldsOccurences($tagNames = array());
    public function isTargetField($occurence);
    public function getTagName($occurence);
    public function getAttribute($occurence, $attributeKey);

}
