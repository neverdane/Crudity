<?php
namespace Neverdane\Crudity\Workflow;

use Neverdane\Crudity\Exception\Exception;
use Neverdane\Crudity\Form\Form;

class Workflow
{

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
     * @param string $rowId
     *  The optional rowId on which to perform the action (if read, update or delete)
     * @return array
     *  Returns an array containing the execution of $action status :
     *  ["status"]  => (bool)   If the action has been executed successfully. Can be :
     *                          - Form::STATUS_SUCCESS
     *                          - Form::STATUS_FAILURE
     *  ["errors"]  => (array)  All errors returned (see in Form for format)
     *  ["fields"]  => (optional array) All fields and values if we want to read (see in Form for format)
     * @throws Exception
     */
    private static function routeAction($submittedForm, $action, $rowId = null)
    {
        // We initialize the response
        $return = array(
            "status" => Form::STATUS_SUCCESS
        );
        switch ($action) {
            case Form::ACTION_CREATE :
                // We initialize error messages and merge them with self::$customMessages if set
                Error::initMessages(self::$customMessages);
                // We Create a row with given self::$requestParams["params"]
                $return = $submittedForm->create(self::$requestParams["params"]);
                break;
            case Form::ACTION_READ :
                // If we want to get the row id data, we get all fields and their values
                $fields = $submittedForm->read($rowId);
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
                Error::initMessages(self::$customMessages);
                // We Update the $rowId with given self::$requestParams["params"]
                $return = $submittedForm->update($rowId, self::$requestParams["params"]);
                break;
            case Form::ACTION_DELETE :
                // If we want to delete the row id
                $return = $submittedForm->delete($rowId);
                break;
            default:
                throw new Exception("The action \"" . $action . "\" is not managed by Crudity");
                break;
        }
        $return["errors"] = $submittedForm->getErrors();
        return $return;
    }

    public function start($form)
    {
        // If a Form has been founded
        if (!is_null($form)) {
            // We set the action to be executed (we consider that if none is declared, the action wanted is creation)
            $action = self::$requestParams["action"] || Form::ACTION_CREATE;
            // We launch the action wanted with the params
            $return = self::routeAction($form, $action, self::$requestParams["row_id"]);
            // If the Form submission has been done through AJAX
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                // We print the result
                echo json_encode($return);
                exit;
            }
        } else {
            // If no declared Form has been founded with this id
            throw new Exception("The submitted Form \"" . self::$requestParams["id"] . "\" has not been declared in the config file : \"" . self::PARAMS_FILE . "\"");
        }
    }

}
