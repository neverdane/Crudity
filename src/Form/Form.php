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

use Neverdane\Crudity\Request\FormRequest;
use Neverdane\Crudity\Response\FormResponse;
use Neverdane\Crudity\View\FormParser;
use Neverdane\Crudity\Field\FieldManager;
use Neverdane\Crudity\Registry;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Form
{
    const RENDER_TYPE_FILE = 1;
    const RENDER_TYPE_HTML = 2;
    const RENDER_TYPE_OBJECTS = 3;

    private $id;
    private $observers = array();
    public $renderType = self::RENDER_TYPE_OBJECTS;
    public $render = null;
    public $persisted = false;

    private $parser = null;

    private $request = null;
    private $response = null;
    private $errorMessages = array();

    public function __construct()
    {
        $this->fieldManager = new FieldManager();
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function addObserver($observer)
    {
        $this->observers[] = $observer;
        return $this->onChange();
    }

    public function setObservers($observers)
    {
        $this->observers = $observers;
        return $this->onChange();
    }

    public function getObservers()
    {
        return $this->observers;
    }

    private function onChange()
    {
        if ($this->persisted === true) {
            $this->persist();
        }
        return $this;
    }

    public function persist()
    {
        $this->persisted = true;
        Registry::store($this->id, $this);
        return $this;
    }

    public function setView($parser)
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * @return FormParser
     */
    public function getView()
    {
        return $this->parser;
    }

    public function setFieldManager($fieldManager)
    {
        $this->fieldManager = $fieldManager;
        return $this;
    }

    /**
     * @return FieldManager
     */
    public function getFieldManager()
    {
        return $this->fieldManager;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        $this->response = new FormResponse();
        return $this;
    }

    /**
     * @return FormRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return FormResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function validate()
    {

    }

    public function filter()
    {

    }

    /**
     * @param array $errorMessages
     * @return  $this
     */
    public function setErrorMessages($errorMessages = array())
    {
        $this->errorMessages = $errorMessages;
        return $this;
    }

}
