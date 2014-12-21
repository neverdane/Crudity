<?php
/**
 * Created by PhpStorm.
 * User: Alban
 * Date: 16/12/2014
 * Time: 21:11
 */

namespace Neverdane\Crudity\Form;

use Neverdane\Crudity\Db;

class RequestManager
{
    /**
     * @var Response
     */
    private $response;
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
     * @param Response $response
     * @param Db\Entity[] $entities
     * @param null|Db\Layer\AdapterInterface $dbAdapter
     */
    public function __construct($request, $response, $entities, $dbAdapter = null)
    {
        $this->dbAdapter = $dbAdapter;
        $this->entities = $entities;
        $this->response = $response;
        $this->affectRequest($request);
    }

    /**
     * Sets the Request object that has to be handled by the Form
     * @param Request $request
     * @return $this
     */
    public function affectRequest($request)
    {
        // We instantiate the Response that will store the result we want to share to the user
        $params = $request->getParams();
        foreach ($this->entities as $entity) {
            $fields = $entity->getFields();
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
                    if (isset($entitiesIds[$join])) {
                        $joinValue = $entitiesIds[$join];
                        $field->setValue($joinValue);
                    }
                }
            }
            $entitiesIds[$entity->getName()] = $entity->create($db);
        }


        // We instantiate a new Db that will handle the Db interaction
        $db = new Db\Db();
        // We set the DbAdapter configured on this Form
        $db->setAdapter($this->dbAdapter);
        // We get the requested params (filtered if done, else the raw ones)
        $data = $this->getRequest()->getParams();
        // We ask the Db to create the row and get the result, it should return the inserted id
        $lastInsertId = $db->createRow($this->entities, $data);
        // We add this inserted id as a response param in order to inform the user
        $this->getResponse()->addParam('created_id', $lastInsertId);
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

}