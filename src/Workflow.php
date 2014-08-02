<?php
namespace Neverdane\Crudity;

use Neverdane\Crudity\Exception\Exception;
use Neverdane\Crudity\Form\Form;

class Workflow
{
    const EVENT_VALIDATION_BEFORE = "beforeValidation";
    const EVENT_VALIDATION_AFTER = "afterValidation";

    // Ceci est le tableau qui va contenir tous les objets qui nous observent.
    protected $observers = array();

    // Dès que cet attribut changera on notifiera les classes observatrices.
    protected $nom;

    public function __construct($form) {
        $this->form = $form;
        $formParams = Crudity::$params["Forms"][$this->form->id];
        if (isset($formParams["observer"])) {
            $observerFile = $formParams["observer"] . ".php";
            include $observerFile;
            $observer = new $formParams["observer"]($this->form);
            $this->attach($observer);
        }
    }

    public function attach($observer)
    {
        $this->observers[] = $observer;
    }

    public function detach($observer)
    {
        if (is_int($key = array_search($observer, $this->observers, true)))
        {
            unset($this->observers[$key]);
        }
    }

    public function notify($event)
    {
        foreach ($this->observers as $observer)
        {
            $observer->$event();
        }
    }

    private function validate() {
        $this->notify(self::EVENT_VALIDATION_BEFORE);
        $this->form->validate();
        $this->notify(self::EVENT_VALIDATION_AFTER);
    }

    /**
     * Launches the $action (CRUD) on the $submittedForm
     * If action is read, update or delete, performs the action on $rowId
     * @param Form $submittedForm
     * @param string $action
     *  The action to perform. Can be :
     *  - Form::ACTION_CREATE
     *  - Form::ACTION_READ
     *  - Form::ACTION_UPDATE
     *  - Form::ACTION_DELETE
     * @return array
     *  Returns an array containing the execution of $action status :
     *  ["status"]  => (bool)   If the action has been executed successfully. Can be :
     *                          - Form::STATUS_SUCCESS
     *                          - Form::STATUS_FAILURE
     *  ["errors"]  => (array)  All errors returned (see in Form for format)
     *  ["fields"]  => (optional array) All fields and values if we want to read (see in Form for format)
     * @throws Exception
     */
    private function dispatch($action, $params, $rowId = null)
    {
        $this->form->setRequest($params, $rowId);
        // We initialize the response
        $return = array(
            "status" => Form::STATUS_SUCCESS
        );

        switch ($action) {
            case Form::ACTION_CREATE :
                // We initialize error messages and merge them with self::$customMessages if set
                Error::initialize();
                // We Create a row with given $requestParams["params"]
                $return = $this->startCreationWorkflow();
                break;
            case Form::ACTION_READ :
                // If we want to get the row id data, we get all fields and their values
                $fields = $this->form->read();
                // If the row was not found
                if (is_null($fields)) {
                    $return["status"] = Form::STATUS_FAILURE;
                    throw new Exception("The row \"" . $rowId . "\" was not founded.");
                }
                // We store the fields and values in the response
                $return["fields"] = $fields;
                break;
            case Form::ACTION_UPDATE :
                // We initialize error messages and merge them with self::$customMessages if set
                Error::initialize();
                // We Update the $rowId with given self::$requestParams["params"]
                $return = $this->startUpdateWorkflow();
                break;
            case Form::ACTION_DELETE :
                // If we want to delete the row id
                $return = $this->startDeletionWorkflow();
                break;
            default:
                throw new Exception("The action \"" . $action . "\" is not managed by Crudity");
                break;
        }
        $return["errors"] = $this->form->getErrors();
        return $return;
    }

    private function startCreationWorkflow() {
        $params = $this->form->transform();
        $return = $this->validate();
        $return["extra"] = array();
        /*if ($return["status"] === self::STATUS_SUCCESS) {
            $this->_cleanParams = $this->filter($params);
            $formParams = Crudity::$params["Forms"][$this->id];

            if (isset($formParams["noDuplicates"])) {

            }

            $return["extra"] = $this->_succeed($action, $rowId, $return);
        }*/
        return $return;
        /*
        $this->validate();
        $this->filter();
        $this->create();*/
    }

    private function startUpdateWorkflow() {
        $this->validate();
        $this->filter();
        $this->update();
    }

    private function startDeletionWorkflow() {
        $this->delete();
    }

    public function start($requestParams)
    {
        // We set the action to be executed (we consider that if none is declared, the action wanted is creation)
        $action = $requestParams["action"] || Form::ACTION_CREATE;
        $rowId = $requestParams["row_id"];
        $params = $requestParams["params"];
        // We launch the action wanted with the params
        $return = $this->dispatch($action, $params, $rowId);
        // If the Form submission has been done through AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // We print the result
            echo json_encode($return);
            exit;
        }
    }

}
