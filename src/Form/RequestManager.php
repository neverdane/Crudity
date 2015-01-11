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
     * @var Response
     */
    private $response;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Db\Entity[]
     */
    private $entities;
    /**
     * @var Db\Layer\AdapterInterface
     */
    private $dbAdapter;

    /**
     * @param Request $request
     * @param null|Response $response
     */
    public function __construct($request, $response = null)
    {
        $this->request = $request;
        $this->response = (!is_null($response)) ? $response : new Response();
    }

    /**
     * @return $this
     */
    public function affectRequest()
    {
        // We instantiate the Response that will store the result we want to share to the user
        $params = $this->getRequest()->getParams();
        foreach ($this->entities as $entity) {
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
        foreach ($this->entities as $entity) {
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
        $db->setAdapter($this->dbAdapter);

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
        $this->response->addParam('created_id', $entitiesIds);
        return $this;
    }

    /**
     * @return Db\Entity[]
     */
    private function getEntitiesByPriority()
    {
        $madeEntities = array();
        $sortedEntities = array();
        while (count($this->entities) > 0) {
            foreach ($this->entities as $entityIndex => $entity) {
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
                    unset($this->entities[$entityIndex]);
                }
            }
        }
        return $sortedEntities;
    }

    /**
     * @param Db\Layer\AdapterInterface $dbAdapter
     * @return $this
     */
    public function setDbAdapter($dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
        return $this;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Db\Entity[] $entities
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
        $this->affectRequest();
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

}