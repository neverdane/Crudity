<?php

/**
 * This library is used in order to parse and interact easily with the DOM
 */
require_once "libs/phpQuery/phpQuery.php";

class Crudity_Application {

    /**
     * The Default Adapter used by Crudity.
     * This adapter assumes that no PHP Framework is used
     */
    const ADAPTER_DEFAULT = "Default";
    /**
     * The Zend Framework 1 Adapter used by Crudity
     * This adapter assumes that Zend Framework is used
     */
    const ADAPTER_ZF1 = "Zf1";
    /**
     * The path to the Crudity config file
     */
    const PARAMS_FILE = "config/config.json";

    /**
     * @var string
     * The prefix used in CSS classes ("crudity" could be used but this one is shorter)
     */
    public static $prefix = "cr";
    /**
     * @var array
     */
    public static $params = array();
    /**
     * @var Crudity_Adapter_Abstract
     * The adapter used by Crudity
     */
    public static $adapter = null;
    /**
     * @var Crudity_Form
     * The instance of the submitted Form
     */
    protected static $_submittedForm = null;
    /**
     * @var array
     */
    protected static $_customMessages = array();
    /**
     * @var array
     */
    protected static $_requestParams = array();

    /**
     * Calls the Form render function and echoes it
     * @param string $partial
     */
    public static function render($partial) {
        // We create a Crudity_Form instance from parsing $partial
        $form = new Crudity_Form($partial);
        // We store the Form instance in session so we can retrieve it on submit
        self::$adapter->store($form->_id, $form);
        // We render the cleaned and filtered form HTML
        echo $form->render();
    }

    /**
     * Sets the Adapter used by Crudity
     * The adapter to choose depends on the Framework used for the project
     * @param string $adapterName
     *  The Adapter name to be used. Can be :
     *  - ADAPTER_DEFAULT
     *  - ADAPTER_ZF1
     */
    public static function setAdapter($adapterName = self::ADAPTER_DEFAULT) {
        // If adapter is ADAPTER_DEFAULT, we include its file the old way
         if ($adapterName === self::ADAPTER_DEFAULT) {
             require_once("Crudity/Crudity/Adapter/Default.php");
         }
        // We construct the Adapter name class
         $adapterClass = "Crudity_Adapter_" . $adapterName;
        // And store its instance in the Application class
         self::$adapter = new $adapterClass();
    }

    /**
     * Launches Crudity, all forms submitted will be analyzed.
     * If they are from Crudity, everything will be managed by Crudity
     * You have to specify before calling this method the adapter to be used, however
     * If a plugin is used, this function is already called by the plugin so no need to call it twice
     * @param string $customParamsFile
     *  An optional config file that overrides default params
     * @param array $customMessages
     *  Optional custom messages overriding the original messages
     * @throws Crudity_Exception
     */
    public static function run($customParamsFile = null, array $customMessages = array()) {
        self::_runInit($customParamsFile, $customMessages);
        // If we detect that a Crudity Form has been submitted (a Crudity Form Id has been launched)
        if (!is_null(self::$_requestParams["id"])) {
            // We let the adapter search for the instance of the declared Form in config with the submitted id if any
            self::$_submittedForm = self::$adapter->get(self::$_requestParams["id"]);
            // If a Form has been founded
            if (!is_null(self::$_submittedForm)) {
                // We set the action to be executed (we consider that if none is declared, the action wanted is creation)
                $action = (!is_null(self::$_requestParams["action"]))
                        ? self::$_requestParams["action"]
                        : Crudity_Form::ACTION_CREATE;
                // We launch the action wanted with the params
                $return =  self::_routeAction($action, self::$_requestParams["row_id"]);
                // If the Form submission has been done through AJAX
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    // We print the result
                    echo json_encode($return); exit;
                }
            } else {
                // If no declared Form has been founded with this id
               throw new Crudity_Exception("The submitted Form \"" . self::$_requestParams["id"] . "\" has not been declared in the config file : \"" . self::PARAMS_FILE . "\"");
            }
        }
    }

    /**
     * We get the potentially submitted params and organize and store them in the self::$_requestParams array :
     *  ["id"]      => (string) The Crudity Form Id
     *  ["action"]  => (string) The action to perform with submitted params
     *  ["row_id"]  => (string) The optional Row Id to affect or read if action is read, update or delete
     *  ["params"]  => (array)  All other params submitted
     * @param $userParams
     *  All params to organize
     */
    protected static function _readRequestParams(array $userParams) {
        // We initialize the params
        self::$_requestParams = array(
            "id"		=> null,
            "action"	=> null,
            "row_id"	=> null,
            "params"	=> array()
        );
        foreach ($userParams as $paramName => $paramValue) {
            switch ($paramName) {
                case "crudity_form_action":
                    self::$_requestParams["action"] = $paramValue;
                    continue;
                case "crudity_form_row_id":
                    self::$_requestParams["row_id"] = $paramValue;
                    continue;
                case "crudity_form_id":
                    self::$_requestParams["id"] = $paramValue;
                    continue;
                default:
                    self::$_requestParams["params"][$paramName] = $paramValue;
                    break;
            }
        }
    }

    /**
     * Launches the $action (CRUD) on the self::$_submittedForm
     * If action is read, update or delete, performs the action on $rowId
     * @param string $action
     *  The action to perform. Can be :
     *  - Crudity_Form::ACTION_POPULATE
     *  - Crudity_Form::ACTION_CREATE
     *  - Crudity_Form::ACTION_UPDATE
     *  - Crudity_Form::ACTION_DELETE
     * @param string $rowId
     *  The optional rowId on which to perform the action (if read, update or delete)
     * @return array
     *  Returns an array containing the execution of $action status :
     *  ["status"]  => (bool)   If the action has been executed successfully. Can be :
     *                          - Crudity_Form::STATUS_SUCCESS
     *                          - Crudity_Form::STATUS_FAILURE
     *  ["errors"]  => (array)  All errors returned (see in Crudity_Form for format)
     *  ["fields"]  => (optional array) All fields and values if we want to read (see in Crudity_Form for format)
     * @throws Crudity_Exception
     */
    protected static function _routeAction($action, $rowId = null)  {
        // We initialize the response
        $return = array(
            "status" => Crudity_Form::STATUS_SUCCESS,
            "errors" => array()
        );
        switch($action) {
            case Crudity_Form::ACTION_CREATE :
                // We initialize error messages and merge them with self::$_customMessages if set
                Crudity_Error::initMessages(self::$_customMessages);
                // We Create a row with given self::$_requestParams["params"]
                $return = self::$_submittedForm->create(self::$_requestParams["params"]);
                break;
            case Crudity_Form::ACTION_READ :
                // If we want to get the row id data, we get all fields and their values
                $fields = self::$_submittedForm->read($rowId);
                // If the row was not found
                if(is_null($fields)) {
                    $return["status"] = Crudity_Form::STATUS_FAILURE;
                    throw new Crudity_Exception("The row \"" . $rowId . "\" was not founded.");
                }
                // We store the fields and values in the response
                $return["fields"] = $fields;
                break;
            case Crudity_Form::ACTION_UPDATE :
                // We initialize error messages and merge them with self::$_customMessages if set
                Crudity_Error::initMessages(self::$_customMessages);
                // We Update the $rowId with given self::$_requestParams["params"]
                $return = self::$_submittedForm->update($rowId, self::$_requestParams["params"]);
                break;
            case Crudity_Form::ACTION_DELETE :
                // If we want to delete the row id
                $return = self::$_submittedForm->delete($rowId);
                break;
            default:
                throw new Crudity_Exception("The action \"" . $action . "\" is not managed by Crudity");
                break;

        }
        return $return;
    }

    /**
     * Initialises the Crudity logic :
     * - sets the Framework Adapter
     * - initializes the autoloader
     * - initializes the session
     * - sets the config
     * - stores the custom messages
     * - read the potentially submitted request params and organize them in self::$_requestParams
     * @param string $customParamsFile
     *  An optional config file that overrides default params
     * @param array $customMessages
     *  Optional custom messages overriding the original messages
     * @throws Crudity_Exception
     */
    protected static function _runInit($customParamsFile = null, array $customMessages = array()) {
        // If no adapter was set, we consider that we're running in a classic environment and not a Framework one
        if(is_null(self::$adapter)) {
            self::setAdapter();
        }

        // We let the adapter use his proper logic for class inclusion
        self::$adapter->manageAutoload();
        // We let the adapter use his proper logic for session management
        self::$adapter->manageSession();

        // We import the settings of Crudity
        // We assume that the Crudity default config file path is self::PARAMS_FILE
        self::$params = json_decode(file_get_contents(realpath(dirname(__FILE__) . "/" . self::PARAMS_FILE)), true);
        if (!is_null($customParamsFile)) {
            if(file_exists($customParamsFile)) {
                $customParamsJson = json_decode(file_get_contents($customParamsFile), true);
                if(is_null($customParamsJson)) {
                    throw new Crudity_Exception("The config file " . $customParamsFile . " is not a valid JSON format");
                }
                // We replace each default param by the custom one
                self::$params = array_replace_recursive(self::$params, $customParamsJson);
            } else {
                throw new Crudity_Exception("The config file was not found in the specified path : " . $customParamsFile);
            }
        }
        // We store the custom messages
        self::$_customMessages = $customMessages;

        // We get the submitted params if any
        self::_readRequestParams(self::$adapter->getRequestParams());
    }

}
