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

use Neverdane\Crudity\Db\Entity;
use Neverdane\Crudity\Field\FieldInterface;
use Neverdane\Crudity\Form\Form;
use Neverdane\Crudity\Form\Parser\Parser;
use Neverdane\Crudity\Form\View;
use Neverdane\Crudity\Field\FieldManager;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Crudity
{
    public static function listen()
    {
        Listener::listen();
    }

    /**
     * Creates an instance of Form
     * By parsing the file given and returns it
     *
     * @param string $file
     *  The path to the file
     * @param null|string $defaultEntityName
     * @return Form
     */
    public static function createFromFile($file = null, $defaultEntityName = null)
    {
        //  we store the file content into a variable
        $html = Helper::getFileAsVariable($file);
        return self::createForm($html, $defaultEntityName);
    }

    /**
     * Creates an instance of Form
     * By parsing the given html and returns it
     *
     * @param string $html
     *  The HTML to parse.
     * @param null|string $defaultEntityName
     * @return Form
     */
    public static function createForm($html, $defaultEntityName = null)
    {
        // We instantiate a FormParser in order to extract required data to build the Crudity Form
        $formParser = new Parser($html, $defaultEntityName);
        // We get the form attribute id
        $formId = $formParser->getId();
        // We get all the detected fields instances
        $entitiesData = $formParser->getEntities();
        $entities = array();
        foreach ($entitiesData as $entityName => $entityData) {
            $entity = new Entity($entityName);
            $entity->setEntity($entityName);
            $fields = array();
            foreach ($entityData['fields'] as $fieldData) {
                $fieldType = $fieldData['type'];
                /** @var FieldInterface $field */
                $field = new $fieldType($fieldData['params']);
                $fields[$field->getName()] = $field;
            }
            $entity->setFields($fields);
            $entities[$entity->getName()] = $entity;
        }
        // We get the html which will be rendered
        $formattedHtml = $formParser->getFormattedHtml();

        // We give the FormView its render
        $formView = new View($formattedHtml);

        // We finally create the Crudity Form and give to it its id, the FieldManager and the FormView instances
        // Then, we store the Crudity Form into session
        $form = new Form();
        $form->setId($formId)
            ->setEntities($entities)
            ->setView($formView);
        return $form;
    }

}
