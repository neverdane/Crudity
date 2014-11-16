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

use Neverdane\Crudity\View\FormView;
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

    public $id;
    public $observers = array();
    public $renderType = self::RENDER_TYPE_OBJECTS;
    public $render = null;
    public $persisted = false;

    private $view = null;

    public function __construct()
    {
        $this->fieldManager = new FieldManager();
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
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

    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * @return FormView
     */
    public function getView()
    {
        return $this->view;
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

}
