<?php
/**
 * Created by PhpStorm.
 * User: Alban
 * Date: 16/12/2014
 * Time: 21:11
 */

namespace Neverdane\Crudity\Form;

use Neverdane\Crudity\Db;
use Neverdane\Crudity\Field\FieldValue;

class RequestManager
{
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
     * @var Form
     */
    private $form;
    /**
     * @var bool
     * Tells if the workflow for this RequestManager must be stopped or not
     */
    private $openedWorkflow = true;
    /**
     * @param Request $request
     * @param Form $form
     * @param null|Response $response
     */
    public function __construct($request, $form, $response = null)
    {
        $this->request = $request;
        $this->form = $form;
        $this->response = (!is_null($response)) ? $response : new Response();
    }

    /**
     * @return $this
     */
    public function affectRequest()
    {
        // We instantiate the Response that will store the result we want to share to the user
        $params = $this->getRequest()->getParams();
        foreach ($this->getForm()->getEntities() as $entity) {
            $fields = $entity->getFields();
            foreach ($fields as $fieldName => $field) {
                foreach ($params as $paramName => $values) {
                    if ($fieldName === $paramName) {
                        if (!is_array($values)) {
                            $values = array($values);
                        }
                        foreach ($values as $index => $value) {
                            $field->setValue(new FieldValue($value), $index);
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Validates the value set on each Field and sets a matching Response
     * @param array $errorMessages
     * @return $this
     */
    public function validate($errorMessages = array())
    {
        // We initialize the response status to success
        $this->response->setStatus(Response::STATUS_SUCCESS);
        foreach ($this->getForm()->getEntities() as $entity) {
            $entity->validate($this->response, $errorMessages);
        }
        return $this;
    }

    /**
     * Filters each field and affects the filtered value in the Request
     * @return $this
     */
    public function filter()
    {
        foreach ($this->getForm()->getEntities() as $entity) {
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
        $db->setAdapter($this->getForm()->getDbAdapter());

        $prioritizedEntities = $this->getEntitiesByPriority();
        $entitiesIds = array();
        foreach ($prioritizedEntities as $entity) {
            $fields = $entity->getFields();
            foreach ($fields as $fieldName => $field) {
                if (!is_null($field->getJoin())) {
                    $join = $field->getJoin();
                    if (isset($entitiesIds[$join][0])) {
                        $joinValue = $entitiesIds[$join][0];
                        $field->setValue($joinValue);
                    }
                }
            }
            $entitiesIds[$entity->getName()] = $entity->create($db);
        }
        // We add this inserted id as a response param in order to inform the user
        $this->getResponse()->addParam('created_id', $entitiesIds);
        return $this;
    }

    /**
     * @return Db\Entity[]
     */
    private function getEntitiesByPriority()
    {
        $madeEntities = array();
        $sortedEntities = array();
        $entities = $this->getForm()->getEntities();
        while (count($entities) > 0) {
            foreach ($entities as $entityIndex => $entity) {
                $dependencies = $entity->getDependencies();
                $wait = false;
                if (!is_null($dependencies)) {
                    foreach ($dependencies as $dependency) {
                        if (!in_array($dependency, $madeEntities)) {
                            $wait = true;
                            break;
                        }
                    }
                }
                if (false === $wait) {
                    $madeEntities[] = $entity->getName();
                    $sortedEntities[] = $entity;
                    unset($entities[$entityIndex]);
                }
            }
        }
        return $sortedEntities;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
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

}