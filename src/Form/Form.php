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
use Neverdane\Crudity\Request\FormRequest;
use Neverdane\Crudity\Response\FormResponse;
use Neverdane\Crudity\Field\FieldManager;
use Neverdane\Crudity\Registry;
use Neverdane\Crudity\View\FormView;

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
     * @var bool
     * Tells if the Form has already been persisted.
     * Used in case we set additional parameters after we persisted it in order to automatically persist these one too
     */
    private $persisted = false;
    /**
     * @var FieldManager
     * The FieldManager stores the fields of the Form
     */
    private $fieldManager;
    /**
     * @var FormView
     * Instance of the view used for the rendering
     */
    private $view = null;
    /**
     * @var FormRequest
     * Instance of the request object keeping trace of the parameters given through the request
     */
    private $request = null;
    /**
     * @var FormResponse
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

    /**
     * @param null|Config $config
     * We can pass a Config object in order to customize this Form
     */
    public function __construct($config = null)
    {
        // The Form needs a fieldManager, we instantiate it by default
        $this->fieldManager = new FieldManager();
        // If no config was given, we instantiate a default one
        $this->config = (!is_null($config)) ? $config : new Config();
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
        return $this->onChange();
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
        return $this->onChange();
    }

    /**
     * Sets an array of Observers
     * @param array $observers
     * @return Form
     */
    public function setObservers($observers)
    {
        $this->observers = $observers;
        return $this->onChange();
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
     * To be triggered when we set something on the Form that have to be persisted
     * If we already persisted the Form, it will persist it again, overriding the previous persisted Form
     * @return $this
     */
    private function onChange()
    {
        // If the Form was already persisted, we have to persist it again
        if (true === $this->persisted) {
            $this->persist();
        }
        return $this;
    }

    /**
     * Persists the Form into the Registry in order to be retrieved when a Request occurs
     * @return $this
     */
    public function persist()
    {
        // We set the Form as persisted
        $this->persisted = true;
        // The key used to store the Form is its id
        Registry::storeForm($this->id, $this);
        return $this;
    }

    /**
     * Sets a FormView to the Form that will handle the rendering
     * @param FormView $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;
        // The FormView needs some Config that is stored in the global Form Config so we retrieve and affect it
        $this->view->setConfig($this->config->getConfig(Config::TYPE_VIEW));
        return $this->onChange();
    }

    /**
     * Returns the FormView set on the Form
     * @return FormView
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
        return $this->onChange();
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
     * Sets the FormRequest object that has to be handled by the Form
     * It also instantiates a FormResponse on the Form
     * Indeed, the FormRequest and FormResponse are really complementary
     * @param FormRequest $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        // We instantiate the FormResponse that will store the result we want to share to the user
        $this->response = new FormResponse();
        return $this;
    }

    /**
     * Returns the FormRequest object set on the Form
     * @return FormRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the FormResponse object set on the Form
     * The FormResponse object is instantiated when we set the FormRequest
     * @return FormResponse
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
        $this->getResponse()->setStatus(FormResponse::STATUS_SUCCESS);
        // We get all the fields we have to validate
        $fields = $this->getFieldManager()->getFields();
        /** @var FieldInterface $field */
        foreach ($fields as $field) {
            // We validate each field and we get its status
            $fieldStatus = $field->validate()->getStatus();
            if ($fieldStatus !== AbstractField::STATUS_SUCCESS) {
                // If the validation fail, we set the response status to error
                $this->getResponse()->setStatus(FormResponse::STATUS_ERROR);
                // We construct the error message that will be displayed to the user
                $message = Error::getMessage(
                    $field->getErrorCode(),
                    $this->getErrorMessages(),
                    $field->getName(),
                    $field->getErrorValidatorName(),
                    $placeholders = array(
                        "value" => $field->getValue(),
                        "fieldName" => $field->getName()
                    )
                );
                // Then we add the error message for this field to the response
                $this->getResponse()->addError($field->getErrorCode(), $message, $field->getName());
            }
        }
        return $this;
    }

    /**
     * Filters each field and affects the filtered value in the Request
     * @return $this
     */
    public function filter()
    {
        $params = $this->getRequest()->getParams();
        $this->getRequest()->setParams($params, FormRequest::PARAMS_FILTERED);
        return $this;
    }

    /**
     * Creates a row depending on the FormRequest set on the Form
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
        $lastInsertId = $db->createRow($this->entity, $data);
        // We as this inserted id to the as a response param in order to inform the user
        $this->getResponse()->addParam('created_id', $lastInsertId);
        return $this;
    }

    public function update()
    {
        $db = new Db\Db();
        $db->setAdapter($this->getDbAdapter());
        $data = $this->getRequest()->getParams();
        $rowId = $this->getRequest()->getRowId();
        $affectedCount = $db->updateRow($this->entity, $rowId, $data);
        $this->getResponse()->addParam('affected_count', $affectedCount);
    }

    public function delete()
    {
        $db = new Db\Db();
        $db->setAdapter($this->getDbAdapter());
        $rowId = $this->getRequest()->getRowId();
        $affectedCount = $db->deleteRow($this->entity, $rowId);
        $this->getResponse()->addParam('affected_count', $affectedCount);
    }

    /**
     * @param array $errorMessages
     * @return  $this
     */
    public function setErrorMessages($errorMessages = array())
    {
        $this->errorMessages = $errorMessages;
        return $this->onChange();
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function setDbAdapterKey($key)
    {
        $this->dbAdapterKey = $key;
        return $this->onChange();
    }

    public function getDbAdapter()
    {
        return Db\Db::retrieveAdapter($this->dbAdapterKey);
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this->onChange();
    }

    public function closeWorkflow()
    {
        $this->openedWorkflow = false;
    }

    public function isWorkflowOpened()
    {
        return $this->openedWorkflow;
    }

    public function affectValuesToFields()
    {
        $values = $this->getRequest()->getParams();
        $this->getFieldManager()->affectValues($values);
        return $this;
    }
}
