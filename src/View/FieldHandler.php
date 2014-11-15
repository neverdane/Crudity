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
use Neverdane\Crudity\Field\FieldInterface;

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
        $this->sortHandledFields();
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
                "FieldInterface.php",
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
                // For integrity, we check if it's really a class
                if(class_exists($className)) {
                    // We add it to the default fields
                    self::$defaultHandledFields[] = $className;
                }
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
        $this->sortHandledFields();
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

    // TODO Document this method which explains why and how we sort handled fields (fields with less identification criteria (only tagName for example) are used as fallback)
    public function sortHandledFields()
    {
        $priorityFields = array();
        $handledFields = array();
        /** @var FieldInterface $handledField */
        foreach($this->handledFields as $index => $handledField)
        {
            $priority = 0;
            $identifiers = $handledField::getIdentifiers();
            if(isset($identifiers["tagName"])) {
                $priority ++;
            }
            if(isset($identifiers["attributes"]) && is_array($identifiers["attributes"])) {
                $priority += count($identifiers["attributes"]);
            }
            $priorityFields[(string) $index] = $priority;
        }
        arsort($priorityFields, SORT_DESC);
        foreach ($priorityFields as $handledFieldIndex => $priorityField) {
            $handledFields[] = $handledField[(int) $handledFieldIndex];
        }
        $this->handledFields = $handledFields;
        return $this;
    }

}
