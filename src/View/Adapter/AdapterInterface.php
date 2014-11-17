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
    /**
     * Sets the html to work on
     * @param string $html
     * @return $this
     */
    public function setHtml($html);
    public function getHtml();

    /**
     * Returns the form id attribute value
     * @return null|string
     */
    public function getFormId();

    /**
     * Returns all fields occurrences
     * @param array $tagNames
     * @return array
     */
    public function getFieldsOccurrences($tagNames = array());

    /**
     * Returns if the given field occurrence can be handled by Crudity
     * @param mixed $occurrence
     * @return bool
     */
    public function isFieldRelevant($occurrence);

    /**
     * Returns the tag name of the given occurrence
     * @param mixed $occurrence
     * @return null|string
     */
    public function getTagName($occurrence);

    /**
     * Returns the given attribute value of the given occurrence
     * @param mixed $occurrence
     * @param string $attributeKey
     * @return null|string
     */
    public function getAttribute($occurrence, $attributeKey);

    /**
     * Sets the attribute's value for the given occurrence
     * Creates the attribute if it does not exist
     * @param mixed $occurrence
     * @param string $attributeKey
     * @param string $attributeValue
     * @return $this
     */
    public function setAttribute($occurrence, $attributeKey, $attributeValue);

    /**
     * Removes the attribute from the given occurrence
     * @param mixed $occurrence
     * @param string $attributeKey
     * @return $this
     */
    public function removeAttribute($occurrence, $attributeKey);

}
