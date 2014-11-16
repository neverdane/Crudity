<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Field;

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
        // In order to identify each field correctly and avoid false positive, we have to sort the handled fields
        // Then, the "fallback" handled fields (input, select...) will be set at the end
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
                "FieldHandler.php",
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

    /**
     * In order to identify each field correctly and avoid false positive, we have to sort the handled fields
     * Then, the "fallback" handled fields (input, select...) will be set at the end
     * @return $this
     */
    private function sortHandledFields()
    {
        $priorityFields = array();
        $handledFields = array();
        /** @var FieldInterface $handledField */
        foreach($this->handledFields as $index => $handledField)
        {
            // Foreach handled field, we establish a priority
            $priority = 0;
            // We get the way this field identifies itself
            $identifiers = $handledField::getIdentifiers();
            // If a field is identified at least by its element name
            if(isset($identifiers["tagName"])) {
                $priority ++;
            }
            // If a field has more criteria than tagName, we increment the priority by each one
            if(isset($identifiers["attributes"]) && is_array($identifiers["attributes"])) {
                $priority += count($identifiers["attributes"]);
            }
            // In order to keep the indexes, we cast the digital index as string
            // And we affect the priority of this handled field in a temporary array
            $priorityFields[(string) $index] = $priority;
        }
        // Now we sort the fields by priority
        arsort($priorityFields, SORT_DESC);
        // And we register each handled field ats his position
        foreach ($priorityFields as $handledFieldIndex => $priorityField) {
            $handledFields[] = $handledField[(int) $handledFieldIndex];
        }
        // Now we ca replace the handled fields by the sorted ones
        $this->handledFields = $handledFields;
        return $this;
    }

}
