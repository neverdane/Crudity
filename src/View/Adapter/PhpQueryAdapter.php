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

    private $doc;
    /**
     * @var \phpQueryObject
     */
    private $formEl;

    public function setHtml($html)
    {
        $this->doc = phpQuery::newDocument($html);
        $this->formEl = $this->doc->find("form");
        return $this;
    }

    public function getFormId()
    {
        return $this->formEl->attr("id");
    }

    public function getFieldsOccurences($tagNames = array())
    {
        $totalOccurences = array();
        foreach ($tagNames as $tagName) {
            // We store all fields in a flatten array (That's why we use array_merge and not [])
            $tagNameElements = $this->formEl->find($tagName);
            $tagNameOccurences = array();
            foreach ($tagNameElements as $tagNameElement) {
                $tagNameOccurences[] = $tagNameElement;
            }
            $totalOccurences = array_merge(
                $totalOccurences,
                $tagNameOccurences
            );
        }
        return $totalOccurences;
    }

    public function isTargetField($occurence)
    {
        // We transform the element as a phpQuery object
        $pqInput = pq($occurence);
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

    public function getTagName($occurence)
    {
        return $occurence->nodeName;
    }

    public function getAttribute($occurence, $attributeKey)
    {
        return pq($occurence)->attr($attributeKey);
    }

}
