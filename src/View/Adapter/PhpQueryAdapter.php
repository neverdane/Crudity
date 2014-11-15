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

use phpQuery;
use Neverdane\Crudity\View\FormView;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class PhpQueryAdapter extends AbstractAdapter
{

    /**
     * @var \phpQueryObject
     */
    private $doc;
    /**
     * @var \phpQueryObject
     */
    private $formEl;

    /**
     * Sets the html to work on
     * @param string $html
     * @return $this
     */
    public function setHtml($html)
    {
        // We store the full html as a phpQueryObject in order to affect it
        $this->doc = phpQuery::newDocument($html);
        // We isolate the form element as a phpQueryObject
        $this->formEl = $this->doc->find("form");
        return $this;
    }

    /**
     * Returns the form id attribute value
     * @return null|string
     */
    public function getFormId()
    {
        return $this->formEl->attr("id");
    }

    /**
     * Returns all fields occurrences matching the given tag names
     * @param array $tagNames
     * @return array
     */
    public function getFieldsOccurrences($tagNames = array())
    {
        // We unite the query with join and we convert the returned phpQueryObject to array
        return iterator_to_array($this->formEl->find(join(",", $tagNames)));
    }

    /**
     * Returns all fields occurrences matching the given tag names
     * @param array $tagNames
     * @return array
     */
    public function getFieldsOccurrencesOld($tagNames = array())
    {
        $totalOccurrences = array();
        foreach ($tagNames as $tagName) {
            $tagNameElements = $this->formEl->find($tagName);
            // We convert the object of founded occurrences (which is a phpQueryObject) to an array
            $tagNameOccurrences = iterator_to_array($tagNameElements);
            // We then merge these tag name occurrences with all the occurrences already founded
            $totalOccurrences = array_merge($totalOccurrences, $tagNameOccurrences);
        }
        return $totalOccurrences;
    }

    public function isTargetField($occurrence)
    {
        // We transform the element as a phpQuery object
        $pqInput = pq($occurrence);
        // We test if the current field analyzed is a Crudity functional field in the aim to not manage them
        if ($pqInput->attr("name") === FormView::$prefix . "_partial"
            || $pqInput->attr("type") === "submit"
            || $pqInput->attr("cr-name") === ""
            || is_null($pqInput->attr("cr-name"))
        ) {
            return false;
        }
        return true;
    }

    public function getTagName($occurrence)
    {
        return $occurrence->nodeName;
    }

    public function getAttribute($occurrence, $attributeKey)
    {
        return pq($occurrence)->attr($attributeKey);
    }

}
