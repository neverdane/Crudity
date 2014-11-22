<?php
namespace Neverdane\Crudity;

use Neverdane\Crudity\Exception\Exception;
use Neverdane\Crudity\Form\Form;
use Neverdane\Crudity\Request\FormRequest;

class Workflow
{
    const EVENT_VALIDATION_BEFORE = "preValidation";
    const EVENT_VALIDATION_AFTER = "postValidation";
    const EVENT_FILTER_BEFORE = "preFilter";
    const EVENT_FILTER_AFTER = "postFilter";
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

    private function send()
    {
        $this->notify(self::EVENT_SEND_BEFORE);
        $this->form->getResponse()->send();
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
            case FormRequest::ACTION_CUSTOM :
                $this->validate();
                $this->filter();
                break;
        }
        $this->send();
        return $this;
    }

}
