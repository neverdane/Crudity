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

use Neverdane\Crudity\Form\Form;
use Neverdane\Crudity\View\FormParser;
use Neverdane\Crudity\View\FormView;
use Neverdane\Crudity\Field\FieldManager;

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
     * @param string $html
     *  The HTML to parse.
     * @return Form
     */
    public static function createForm($html)
    {
        // We instantiate a FormParser in order to extract required data to build the Crudity Form
        $formParser = new FormParser($html);
        // We get the form attribute id
        $formId = $formParser->getId();
        // We get all the detected fields instances
        $formFields = $formParser->getFields();
        // We get the html which will be rendered
        $formattedHtml = $formParser->getFormattedHtml();

        // We give the FormView its render
        $formView = new FormView($formattedHtml);
        // The FieldManager handles all that is related to the fields
        $fieldManager = new FieldManager($formFields);

        // We finally create the Crudity Form and give to it its id, the FieldManager and the FormView instances
        // Then, we store the Crudity Form into session
        $form = new Form();
        $form->setId($formId)
            ->setFieldManager($fieldManager)
            ->setView($formView)
            ->persist();
        return $form;
    }

}
