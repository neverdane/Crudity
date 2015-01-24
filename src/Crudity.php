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

use Neverdane\Crudity\Db\EntityFactory;
use Neverdane\Crudity\Field\FieldFactory;
use Neverdane\Crudity\Form\Form;
use Neverdane\Crudity\Form\Parser\Parser;
use Neverdane\Crudity\Form\View;

/**
 * This class is a Facade that eases Crudity manipulation
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Crudity
{
    /**
     * Triggers the listening of the Request
     *
     * @param null|Registry $registry
     *  The registry instance which embeds the Form instances
     */
    public static function listen($registry = null)
    {
        $listener = new Listener();
        $listener->listen($registry);
    }

    /**
     * Creates an instance of Form
     * By parsing the given file and returns it
     *
     * @param string $file
     *  The path to the HTML file
     * @param null|string $defaultEntityName
     *  The entity name that will be used when no entity was set on a Field
     * @return Form
     */
    public static function createFromFile($file, $defaultEntityName = null)
    {
        // We store the file content into a variable
        $html = Helper::getFileAsVariable($file);
        return self::createForm($html, $defaultEntityName);
    }

    /**
     * Creates an instance of Form
     * By parsing the given html and returns it
     *
     * @param string $html
     *  The HTML to parse
     * @param null|string $defaultEntityName
     *  The entity name that will be used when no entity was set on a Field
     * @return Form
     */
    public static function createForm($html, $defaultEntityName = null)
    {
        $entityFactory = new EntityFactory();
        // We instantiate a FormParser in order to extract required data to build the Crudity Form
        $formParser = new Parser($html);

        // We get the id attribute of the form
        $formId = $formParser->getId();
        // We get all the detected entities and their fields data
        $entitiesData = $formParser->getEntitiesData($defaultEntityName);
        // We get the html which will be rendered
        $formattedHtml = $formParser->getFormattedHtml();

        // We create the Entities and their Fields according to the parsed data
        $entities = $entityFactory->createEntities($entitiesData);
        // We give the FormView its render
        $formView = new View($formattedHtml);

        // We finally create the Crudity Form and set its id, its Entities and its View instance
        $form = new Form();
        $form->setId($formId)
            ->setEntities($entities)
            ->setView($formView);
        return $form;
    }

}
