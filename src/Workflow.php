<?php
namespace Neverdane\Crudity;

use Neverdane\Crudity\Form\Request;
use Neverdane\Crudity\Form\RequestManager;
use Neverdane\Crudity\Form\Response;

class Workflow
{
    const EVENT_VALIDATION_BEFORE = "preValidation";
    const EVENT_VALIDATION_AFTER = "postValidation";
    const EVENT_FILTER_BEFORE = "preFilter";
    const EVENT_FILTER_AFTER = "postFilter";
    const EVENT_PROCESS_BEFORE = "preProcess";
    const EVENT_PROCESS_AFTER = "postProcess";
    const EVENT_CREATE_BEFORE = "preCreate";
    const EVENT_CREATE_AFTER = "postCreate";
    const EVENT_UPDATE_BEFORE = "preUpdate";
    const EVENT_UPDATE_AFTER = "postUpdate";
    const EVENT_DELETE_BEFORE = "preDelete";
    const EVENT_DELETE_AFTER = "postDelete";
    const EVENT_READ_BEFORE = "preRead";
    const EVENT_READ_AFTER = "postRead";
    const EVENT_SEND_BEFORE = "preSend";

    /**
     * @var null|RequestManager
     */
    private $requestManager;

    /**
     * @param RequestManager $requestManager
     */
    public function __construct($requestManager)
    {
        $this->requestManager = $requestManager;
    }

    private function validate()
    {
        if ($this->getRequestManager()->isWorkflowOpened()) {
            Error::initialize();
            $this->notify(self::EVENT_VALIDATION_BEFORE);
            $this->getRequestManager()->validate();
            if (Response::STATUS_ERROR === $this->getRequestManager()->getResponse()->getStatus()) {
                $this->getRequestManager()->closeWorkflow();
            }
            $this->notify(self::EVENT_VALIDATION_AFTER);
        }
    }

    private function filter()
    {
        if ($this->getRequestManager()->isWorkflowOpened()) {
            $this->notify(self::EVENT_FILTER_BEFORE);
            $this->getRequestManager()->filter();
            $this->notify(self::EVENT_FILTER_AFTER);
        }
    }

    private function process($type)
    {
        if ($this->getRequestManager()->isWorkflowOpened()) {
            $this->notify(self::EVENT_PROCESS_BEFORE);
            switch ($type) {
                case Request::ACTION_CREATE :
                    $this->create();
                    break;
                case Request::ACTION_UPDATE :
                    $this->update();
                    break;
            }
            $this->notify(self::EVENT_PROCESS_AFTER);
        }
    }

    private function create()
    {
        if ($this->getRequestManager()->isWorkflowOpened()) {
            $this->notify(self::EVENT_CREATE_BEFORE);
            $this->getRequestManager()->create();
            $this->notify(self::EVENT_CREATE_AFTER);
        }
    }

    private function update()
    {
        if ($this->getRequestManager()->isWorkflowOpened()) {
            $this->notify(self::EVENT_UPDATE_BEFORE);
            $this->getRequestManager()->update();
            $this->notify(self::EVENT_UPDATE_AFTER);
        }
    }

    private function delete()
    {
        if ($this->getRequestManager()->isWorkflowOpened()) {
            $this->notify(self::EVENT_DELETE_BEFORE);
            $this->getRequestManager()->delete();
            $this->notify(self::EVENT_DELETE_AFTER);
        }
    }

    private function read()
    {
        if ($this->getRequestManager()->isWorkflowOpened()) {
            $this->notify(self::EVENT_READ_BEFORE);
            $this->getRequestManager()->read();
            $this->notify(self::EVENT_READ_AFTER);
        }
    }

    private function send()
    {
        $this->notify(self::EVENT_SEND_BEFORE);
        $this->getRequestManager()->getResponse()->send();
    }

    private function notify($event)
    {
        $observers = $this->getRequestManager()->getForm()->getObservers();
        foreach ($observers as $observer) {
            $observer->$event($this->getRequestManager());
        }

    }

    public function start()
    {
        $action = $this->getRequestManager()->getRequest()->getAction();
        switch ($action) {
            default:
                $this->getRequestManager()->getRequest()->setAction(Request::ACTION_CUSTOM);
            case Request::ACTION_CUSTOM :
                $this->validate();
                $this->filter();
                break;
            case Request::ACTION_CREATE :
            case Request::ACTION_UPDATE :
                $this->validate();
                $this->filter();
                $this->process($action);
                break;
            case Request::ACTION_DELETE :
                $this->delete();
                break;
            case Request::ACTION_READ :
                $this->read();
                break;
        }
        $this->send();
        return $this;
    }

    /**
     * @return RequestManager|null
     */
    public function getRequestManager()
    {
        return $this->requestManager;
    }

}
