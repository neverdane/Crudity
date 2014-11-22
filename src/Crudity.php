<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity;

use Neverdane\Crudity\Field\FieldManager;
use Neverdane\Crudity\Form\Form;
use Neverdane\Crudity\View\FormParser;
use Neverdane\Crudity\View\FormView;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Crudity
{

    private static $configFile = null;

    public static function setConfig($configFile)
    {
        self::$configFile = $configFile;
    }

    public static function listen()
    {

    }

    /**
     * Creates an instance of Form
     * By parsing the file given and returns it
     *
     * @param string $file
     *  The path to the file
     * @return Form
     */
    public static function createFromFile($file = null)
    {
        //  we store the file content into a variable
        $html = Helper::getFileAsVariable($file);
        return self::createForm($html);
    }

    /**
     * Creates an instance of Form
     * By parsing the given html and returns it
     *
     * @param $html
     *  The HTML to parse.
     * @return Form
     */
    public static function createForm($html)
    {
        $formParser = new FormParser($html);
        $formId = $formParser->getId();
        $formFields = $formParser->getFields();
        $formattedHtml = $formParser->getFormattedHtml();

        $formView = new FormView($formattedHtml);
        $fieldManager = new FieldManager($formFields);

        $form = new Form();
        $form->setFieldManager($fieldManager)
            ->setView($formView)
            ->setId($formId)
            ->persist();
        return $form;
    }

}
