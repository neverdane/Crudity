<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Form;

use Neverdane\Crudity\AbstractObserver;
use Neverdane\Crudity\Db;
use Neverdane\Crudity\Error;
use Neverdane\Crudity\Field\AbstractField;
use Neverdane\Crudity\Field\FieldInterface;
use Neverdane\Crudity\Field\FieldManager;
use Neverdane\Crudity\Registry;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Form
{
    /**
     * @var string
     * The id that identifies the Form in the Registry
     */
    private $id;
    /**
     * @var array
     * The observers added to the Form in order to affect it during the workflow
     */
    private $observers = array();
    /**
     * @var FieldManager
     * The FieldManager stores the fields of the Form
     */
    private $fieldManager;
    /**
     * @var View
     * Instance of the view used for the rendering
     */
    private $view = null;
    /**
     * @var Request
     * Instance of the request object keeping trace of the parameters given through the request
     */
    private $request = null;
    /**
     * @var Response
     * Instance of the response object that contains everything we want to send to the client after the workflow process
     */
    private $response = null;
    /**
     * @var array
     * An array of customized errors for some fields or validators on this Form
     */
    private $errorMessages = array();
    /**
     * @var string
     * The adapter key matching the dbLayerAdapter instance we want to use on this Form
     */
    private $dbAdapterKey = null;
    /**
     * @var mixed
     * The entity object the Form is working with. Depends on the DbLayerAdapter used
     */
    private $entity;

    /**
     * @var bool
     * Tells if the workflow for this Form must be stopped or not
     */
    private $openedWorkflow = true;
    private $supportingEntities = null;
    /**
     * @var Db\Entity[] array
     */
    private $entities = array();

    /**
     * @param null|Config $config
     * @param null|FieldManager $fieldManager
     * We can pass a Config object in order to customize this Form
     */
    public function __construct($config = null, $fieldManager = null)
    {
        // If no config was given, we instantiate a default one
        $this->config = (!is_null($config)) ? $config : new Config();
        // The Form needs a fieldManager, we instantiate it by default
        $this->fieldManager = (!is_null($fieldManager)) ? $fieldManager : new FieldManager();
    }

    /**
     * Sets the id of the Form
     * This id is the key used to store the Form in the Registry
     * @param string $id
     * @return Form
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Gets the id of the Form
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Adds an Observer to the Form that let us listen to events during the workflow process
     * @param AbstractObserver $observer
     * @return Form
     */
    public function addObserver($observer)
    {
        $this->observers[] = $observer;
        return $this;
    }

    /**
     * Sets an array of Observers
     * @param array $observers
     * @return Form
     */
    public function setObservers($observers)
    {
        $this->observers = $observers;
        return $this;
    }

    /**
     * Returns all the Observers set on the Form
     * @return array
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * Sets a View to the Form that will handle the rendering
     * @param View $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;
        // The View needs some Config that is stored in the global Form Config so we retrieve and affect it
        $this->view->setConfig($this->config->getConfig(Config::TYPE_VIEW));
        return $this;
    }

    /**
     * Returns the View set on the Form
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Sets the FieldManager that will be used to store the Fields for the Form
     * @param FieldManager $fieldManager
     * @return Form
     */
    public function setFieldManager($fieldManager)
    {
        $this->fieldManager = $fieldManager;
        return $this;
    }

    /**
     * Returns the FieldManager set on the Form
     * @return FieldManager
     */
    public function getFieldManager()
    {
        return $this->fieldManager;
    }

    /**
     * Sets the Request object that has to be handled by the Form
     * It also instantiates a Response on the Form
     * Indeed, the Request and Response are really complementary
     * @param Request $request
     * @param Response $response
     * @return $this
     */
    public function setRequest($request, $response = null)
    {
        $this->request = $request;
        // We instantiate the Response that will store the result we want to share to the user
        $this->response = (!is_null($response)) ? $response : new Response();
        $params = $request->getParams();
        /** @var Db\Entity $entity */
        foreach ($this->entities as $entity) {
            $fields = $entity->getFields();
            /** @var FieldInterface $field */
            foreach ($fields as $fieldName => $field) {
                foreach ($params as $paramName => $value) {
                    if ($fieldName === $paramName) {
                        $field->setValue($value);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Returns the Request object set on the Form
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the Response object set on the Form
     * The Response object is instantiated when we set the Request
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Validates the value set on each Field and sets a matching Response
     * @return $this
     */
    public function validate()
    {
        // We initialize the response status to success
        $this->getResponse()->setStatus(Response::STATUS_SUCCESS);

        foreach ($this->entities as $entity) {
            $entity->validate($this->getResponse(), $this->getErrorMessages());
        }
        return $this;
    }

    /**
     * Filters each field and affects the filtered value in the Request
     * @return $this
     */
    public function filter()
    {
        foreach ($this->entities as $entity) {
            $entity->filter();
        }
        return $this;
    }

    /**
     * Creates a row depending on the Request set on the Form
     * @return $this
     */
    public function create()
    {
        // We instantiate a new Db that will handle the Db interaction
        $db = new Db\Db();
        // We set the DbAdapter configured on this Form
        $db->setAdapter($this->getDbAdapter());
        // We get the requested params (filtered if done, else the raw ones)
        $data = $this->getRequest()->getParams();
        // We ask the Db to create the row and get the result, it should return the inserted id
        $lastInsertId = $db->createRow($this->entities, $data);
        // We add this inserted id as a response param in order to inform the user
        $this->getResponse()->addParam('created_id', $lastInsertId);
        return $this;
    }

    /**
     * Update the row depending on the Request set on the Form
     * @return $this
     */
    public function update()
    {
        // We instantiate a new Db that will handle the Db interaction
        $db = new Db\Db();
        // We set the DbAdapter configured on this Form
        $db->setAdapter($this->getDbAdapter());
        // We get the requested params (filtered if done, else the raw ones)
        $data = $this->getRequest()->getParams();
        // We get the requested row id we want to update
        $rowId = $this->getRequest()->getRowId();
        // We ask the Db to update the row and get the result, it should return the number of affected rows
        $affectedCount = $db->updateRow($this->entity, $rowId, $data);
        // We add the number of affected rows as a response param in order to inform the user
        $this->getResponse()->addParam('affected_count', $affectedCount);
    }

    /**
     * Update the row depending on the Request set on the Form
     * @return $this
     */
    public function delete()
    {
        // We instantiate a new Db that will handle the Db interaction
        $db = new Db\Db();
        // We set the DbAdapter configured on this Form
        $db->setAdapter($this->getDbAdapter());
        // We get the requested row id we want to update
        $rowId = $this->getRequest()->getRowId();
        // We ask the Db to delete the row and get the result, it should return the number of affected rows
        $affectedCount = $db->deleteRow($this->entity, $rowId);
        // We add the number of affected rows as a response param in order to inform the user
        $this->getResponse()->addParam('affected_count', $affectedCount);
    }

    /**
     * Sets an array of custom Error Messages overriding the default ones.
     * The array must be formatted as below :
     *
     *      array(
     *          "Fields" => array(
     *              "{Field Name}" => array(
     *                  {ERROR CODE} => "{Your message}"
     *              )
     *          ),
     *          "Validators" => array(
     *              "{Validator Name}" => array(
     *                  {ERROR CODE} => "{Your message}"
     *              )
     *          )
     *      );
     *
     * @param array $errorMessages
     * @return  $this
     */
    public function setErrorMessages($errorMessages)
    {
        $this->errorMessages = $errorMessages;
        return $this;
    }

    /**
     * Returns the customized Error Messages set on the Form
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * Returns the Config Object set on the Form
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets the adapter key that will be used to retrieve the DbAdapter registered in the Db
     * @param string $key
     * @return Form
     */
    public function setDbAdapterKey($key)
    {
        $this->dbAdapterKey = $key;
        return $this;
    }

    /**
     * Returns the DbAdapter registered in the Db in the Db from the dbAdapterKey set on the Form
     * The adapter is not directly set on the Form because it embeds the connection that must not be stored in session
     * If no dbAdapterKey was set on the Form, the method will return the default dbAdapter registered on Db if any
     * @return Db\Layer\AdapterInterface|null
     */
    public function getDbAdapter()
    {
        return Db\Db::retrieveAdapter($this->dbAdapterKey);
    }

    /**
     * Sets the entity the Form could work with on Db workflow,
     * (e.g. When we work with pdo and mysql, the entity will be a table name)
     * @param mixed $entity
     * @return Form
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @param Db\Entity $entity
     * @return $this
     */
    public function addSupportingEntity($entity)
    {
        $this->supportingEntities[$entity->getName()] = $entity;
        return $this;
    }

    /**
     * Avoids the Workflow to continue
     * Useful when an error occurs
     */
    public function closeWorkflow()
    {
        $this->openedWorkflow = false;
    }

    /**
     * Returns whether the Workflow can continue or not
     * @return bool
     */
    public function isWorkflowOpened()
    {
        return $this->openedWorkflow;
    }

    /**
     * Affects the values from the Request to each matching Field
     * This method is called before the validation of the filtering
     * @return $this
     */
    public function affectValuesToFields()
    {
        // We get the values we want to affect
        $values = $this->getRequest()->getParams();
        // We ask the FieldManager to affect these values to their matching Field
        $this->getFieldManager()->affectValues($values);
        return $this;
    }

    /**
     * @param array $entities
     * @return $this
     */
    public function setEntities($entities)
    {
        /** @var Db\Entity $entity */
        foreach ($entities as $entity) {
            $this->entities[$entity->getName()] = $entity;
        }
        return $this;
    }

    /**
     * @param string $entityName
     * @return null|Db\Entity
     */
    public function getEntity($entityName = 'default')
    {
        return isset($this->entities[$entityName])
            ? $this->entities[$entityName]
            : null;
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }
}
