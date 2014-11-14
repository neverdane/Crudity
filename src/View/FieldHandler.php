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
        // We set the Fields classes handled by Crudity by default
        $this->handledFields = $this->getDefaultHandledFields();
    }

    /**
     * Returns the fields classes that Crudity handled by default
     * @return array
     */
    private function getDefaultHandledFields()
    {
        // We init the default fields if they're not already
        if (is_null(self::$defaultHandledFields)) {
            self::$defaultHandledFields = array();
            // We get the folder name of the Fields classes
            $fieldsDirectory = dirname(__FILE__) . "/../Field";
            // We get the namespace of this folder from the current class
            $fieldsNamespace = str_replace("\\View", "",  __NAMESPACE__) . "\\Field\\";
            // We set the files that are not fields in this folder
            $excludedFiles = array(
                ".",
                "..",
                "AbstractField.php",
                "FieldManager.php"
            );
            // scandir returns all the files of the folder in an array
            $directory = scandir($fieldsDirectory);
            foreach ($directory as $fileName) {
                // If the file is not considered as a Field or is a directory, we ignore it
                if (in_array($fileName, $excludedFiles) || is_dir($fileName)) {
                    continue;
                }
                // We isolate the class name from the extension
                $fileParts = explode(".", $fileName);
                // And we reconstruct the class name (with namespace) of the found field
                $className = $fieldsNamespace . $fileParts[0];
                // We add it to the default fields
                self::$defaultHandledFields[] = $className;
            }
        }
        return self::$defaultHandledFields;
    }

    /**
     * We can add a custom field to be handled by Crudity with this method
     * @param $fieldClassName
     * @return $this
     */
    public function addHandledField($fieldClassName)
    {
        $this->handledFields[] = $fieldClassName;
        return $this;
    }

    /**
     * Returns all the fields handled by Crudity
     * @return array
     */
    public function getHandledFields()
    {
        return $this->handledFields;
    }

}
