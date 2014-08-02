<?php
namespace Neverdane\Crudity;

/**
 * This library is used in order to parse and interact easily with the DOM
 */
use Neverdane\Crudity\Exception\Exception;
use Neverdane\Crudity\Form\Form;
use Neverdane\Crudity\Adapter\AbstractAdapter;

//require_once __DIR__ . "/../../libs/phpQuery/phpQuery.php";

class Listener
{

    public static function listen() {
        // We get the submitted params if any
        $requestParams = self::getRequestParams();
        // If we detect that a Crudity Form has been submitted (a Crudity Form Id has been launched)
        if (self::wasCrudityFormSubmitted($requestParams)) {
            // We let the adapter search for the instance of the declared Form in config with the submitted id if any
            $submittedForm = Crudity::$adapter->get($requestParams["id"]);
            // If a Form has been founded
            if (!is_null($submittedForm)) {
                $workflow = new Workflow($submittedForm);
                $workflow->start($requestParams);
            } else {
                // If no declared Form has been founded with this id
                throw new Exception("The submitted Form \"" . $requestParams["id"] . "\" has not been declared in the config file : \"" . Crudity::PARAMS_FILE . "\"");
            }
        }
    }

    /**
     * We get the potentially submitted params and organize and store them in the $requestParams array :
     *  ["id"]      => (string) The Crudity Form Id
     *  ["action"]  => (string) The action to perform with submitted params
     *  ["row_id"]  => (string) The optional Row Id to affect or read if action is read, update or delete
     *  ["params"]  => (array)  All other params submitted
     */
    private static function getRequestParams()
    {
        $userParams = Crudity::$adapter->getRequestParams();
        // We initialize the params
        $requestParams = array(
            "id" => null,
            "action" => null,
            "row_id" => null,
            "params" => array()
        );
        foreach ($userParams as $paramName => $paramValue) {
            switch ($paramName) {
                case "crudity_form_action":
                    $requestParams["action"] = $paramValue;
                    continue;
                case "crudity_form_row_id":
                    $requestParams["row_id"] = $paramValue;
                    continue;
                case "crudity_form_id":
                    $requestParams["id"] = $paramValue;
                    continue;
                default:
                    $requestParams["params"][$paramName] = $paramValue;
                    break;
            }
        }
        return $requestParams;
    }

    /**
     * Detects if a Crudity Form has been submitted (a Crudity Form Id has been sent)
     * @return bool
     */
    private static function wasCrudityFormSubmitted($requestParams)
    {
        return !is_null($requestParams["id"]);
    }

}
