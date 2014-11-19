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
use Neverdane\Crudity\View\FormFormatter;
use Neverdane\Crudity\View\FormParser;
use Neverdane\Crudity\View\FormView;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Crudity
{
    const INPUT_TYPE_FILE = 1;
    const INPUT_TYPE_HTML = 2;

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
        return self::createForm($file, self::INPUT_TYPE_FILE);
    }

    /**
     * Creates an instance of Form
     * By parsing the HTML given and returns it
     *
     * @param string $html
     * @return Form
     */
    public static function createFromHtml($html = null)
    {
        return self::createForm($html, self::INPUT_TYPE_HTML);
    }

    /**
     * Creates an instance of Form
     * By parsing the input given and returns it
     *
     * @param $input
     *  The input to parse. Could be a file or raw HTML
     * @param $inputType
     *  The type of input to parse
     * @return Form
     */
    private static function createForm($input, $inputType)
    {
        if ($inputType === self::INPUT_TYPE_FILE) {
            // If the input type is a file, we store its content into a variable
            $input = Helper::getFileAsVariable($input);
        }

        $formParser = new FormParser($input);
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
