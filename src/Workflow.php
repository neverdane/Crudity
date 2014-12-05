<?php
namespace Neverdane\Crudity;

use Neverdane\Crudity\Field\FieldInterface;
use Neverdane\Crudity\Form\Form;
use Neverdane\Crudity\Request\FormRequest;

class Workflow
{
    const EVENT_VALIDATION_BEFORE = "preValidation";
    const EVENT_VALIDATION_AFTER = "postValidation";
    const EVENT_FILTER_BEFORE = "preFilter";
    const EVENT_FILTER_AFTER = "postFilter";
    const EVENT_CREATE_BEFORE = "preCreate";
    const EVENT_CREATE_AFTER = "postCreate";
    const EVENT_UPDATE_BEFORE = "preUpdate";
    const EVENT_UPDATE_AFTER = "postUpdate";
    const EVENT_DELETE_BEFORE = "preDelete";
    const EVENT_DELETE_AFTER = "postDelete";
    const EVENT_SEND_BEFORE = "preSend";

    private $form = null;

    /**
     * @param Form $form
     */
    public function __construct($form)
    {
        $this->form = $form;
    }

    private function validate()
    {
        Error::initialize();
        $this->notify(self::EVENT_VALIDATION_BEFORE);
        $this->form->validate();
        $this->notify(self::EVENT_VALIDATION_AFTER);
    }

    private function filter()
    {
        $this->notify(self::EVENT_FILTER_BEFORE);
        $this->form->filter();
        $this->notify(self::EVENT_FILTER_AFTER);
    }

    private function process($type)
    {
        switch($type) {
            case FormRequest::ACTION_CREATE :
                $this->create();
                break;
            case FormRequest::ACTION_UPDATE :
                $this->update();
                break;
        }
    }

    private function create()
    {
        $this->notify(self::EVENT_CREATE_BEFORE);
        $this->form->create();
        $this->notify(self::EVENT_CREATE_AFTER);
    }

    private function update()
    {
        $this->notify(self::EVENT_UPDATE_BEFORE);
        $this->form->update();
        $this->notify(self::EVENT_UPDATE_AFTER);
    }

    private function delete()
    {
        $this->notify(self::EVENT_DELETE_BEFORE);
        $this->form->delete();
        $this->notify(self::EVENT_DELETE_AFTER);
    }

    private function send()
    {
        $this->notify(self::EVENT_SEND_BEFORE);
        $this->form->getResponse()->send();
    }

    public function affectValues()
    {
        $fields = $this->form->getFieldManager()->getFields();
        $values = $this->form->getRequest()->getParams();
        /** @var FieldInterface $field */
        foreach($fields as $field)
        {
            if(isset($values[$field->getName()])) {
                $field->setValue($values[$field->getName()]);
            }
        }
        return $this;
    }

    private function notify($event)
    {
        $observers = $this->form->getObservers();
        foreach ($observers as $observer) {
            $observer->$event($this->form);
        }

    }

    public function start()
    {
        $action = $this->form->getRequest()->getAction();

        switch ($action) {
            default:
                $this->form->getRequest()->setAction(FormRequest::ACTION_CUSTOM);
            case FormRequest::ACTION_CUSTOM :
                $this->affectValues();
                $this->validate();
                $this->filter();
                break;
            case FormRequest::ACTION_CREATE :
            case FormRequest::ACTION_UPDATE :
                $this->affectValues();
                $this->validate();
                $this->filter();
                $this->process($action);
                break;
        }
        $this->send();
        return $this;
    }

}
