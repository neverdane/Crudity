<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\View;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class FieldHandler
{
    public $fields = array();

    private static $defaultHandledFields = null;
    private $handledFields = null;

    public function __construct()
    {
        $this->handledFields = $this->getDefaultHandledFields();
    }

    private function getDefaultHandledFields()
    {
        if (is_null(self::$defaultHandledFields)) {
            self::$defaultHandledFields = array();
            $fieldsDirectory = dirname(__FILE__) . "/../Field";
            $fieldsNamespace = str_replace("\\View", "",  __NAMESPACE__) . "\\Field\\";
            $excludedFiles = array(
                ".",
                "..",
                "AbstractField.php",
                "FieldManager.php"
            );
            $directory = scandir($fieldsDirectory);
            foreach ($directory as $fileName) {
                if (in_array($fileName, $excludedFiles) || is_dir($fileName)) {
                    continue;
                }
                $fileParts = explode(".", $fileName);
                $className = $fieldsNamespace . $fileParts[0];
                self::$defaultHandledFields[] = $className;
            }
        }
        return self::$defaultHandledFields;
    }

    public function addHandledField($fieldClassName)
    {
        $this->handledFields[] = $fieldClassName;
    }

    public function getHandledFields()
    {
        return $this->handledFields;
    }

}
