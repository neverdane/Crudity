<?php
namespace Neverdane\Crudity;

/**
 * This library is used in order to parse and interact easily with the DOM
 */
use Neverdane\Crudity\Exception\Exception;
use Neverdane\Crudity\Form\Form;
use Neverdane\Crudity\Workflow;
use Neverdane\Crudity\Adapter\AbstractAdapter;

//require_once __DIR__ . "/../../libs/phpQuery/phpQuery.php";

class Crudity
{

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
     * @var AbstractAdapter
     * The adapter used by Crudity
     */
    public static $adapter = null;
    /**
     * @var array
     */
    private static $customMessages = array();
    /**
     * @var array
     */
    private static $requestParams = array();

    /**
     * Detects if a Crudity Form has been submitted (a Crudity Form Id has been sent)
     * @return bool
     */
    private function wasCrudityFormSubmitted()
    {
        return !is_null(self::$requestParams["id"]);
    }

    /**
     * We get the potentially submitted params and organize and store them in the self::$requestParams array :
     *  ["id"]      => (string) The Crudity Form Id
     *  ["action"]  => (string) The action to perform with submitted params
     *  ["row_id"]  => (string) The optional Row Id to affect or read if action is read, update or delete
     *  ["params"]  => (array)  All other params submitted
     */
    private static function readRequestParams()
    {
        $userParams = self::$adapter->getRequestParams();
        // We initialize the params
        self::$requestParams = array(
            "id" => null,
            "action" => null,
            "row_id" => null,
            "params" => array()
        );
        foreach ($userParams as $paramName => $paramValue) {
            switch ($paramName) {
                case "crudity_form_action":
                    self::$requestParams["action"] = $paramValue;
                    continue;
                case "crudity_form_row_id":
                    self::$requestParams["row_id"] = $paramValue;
                    continue;
                case "crudity_form_id":
                    self::$requestParams["id"] = $paramValue;
                    continue;
                default:
                    self::$requestParams["params"][$paramName] = $paramValue;
                    break;
            }
        }
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

    /**
     * Initialises the Crudity logic :
     * - sets the Framework Adapter
     * - initializes the session
     * - sets the config
     * - stores the custom messages
     * @param string $customParamsFile
     *  An optional config file that overrides default params
     * @param array $customMessages
     *  Optional custom messages overriding the original messages
     * @throws Exception
     */
    private static function initialize($customParamsFile = null, array $customMessages = array())
    {
        // If no adapter was set, we consider that we're running in a classic environment and not a Framework one
        if (is_null(self::$adapter)) {
            self::setAdapter();
        }

        // We let the adapter use his proper logic for session management
        self::$adapter->manageSession();

        self::initializeParams($customParamsFile);
        self::$customMessages = $customMessages;
    }

    private function initializeParams($customParamsFile)
    {
        // We import the settings of Crudity
        // We assume that the Crudity default config file path is self::PARAMS_FILE
        self::$params = json_decode(file_get_contents(realpath(dirname(__FILE__) . "/" . self::PARAMS_FILE)), true);
        if (!is_null($customParamsFile)) {
            if (file_exists($customParamsFile)) {
                $customParamsJson = json_decode(file_get_contents($customParamsFile), true);
                if (is_null($customParamsJson)) {
                    throw new Exception("The config file " . $customParamsFile . " is not a valid JSON format");
                }
                // We replace each default param by the custom one
                self::$params = array_replace_recursive(self::$params, $customParamsJson);
            } else {
                throw new Exception("The config file was not found in the specified path : " . $customParamsFile);
            }
        }
    }

    private function handleFormSubmission($form)
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

    /**
     * Calls the Form render function and echoes it
     * @param string $partial
     */
    public static function render($partial)
    {
        // We create a _Form instance from parsing $partial
        $form = new Form($partial);
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
    public static function setAdapter($adapterName = self::ADAPTER_DEFAULT)
    {
        // We construct the Adapter name class
        $adapterClass = __NAMESPACE__ . "\\Adapter\\" . $adapterName . "Adapter";
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
     * @throws Exception
     */
    public static function run($customParamsFile = null, array $customMessages = array())
    {
        // We intialize the Crudity environment (config files, session...)
        self::initialize($customParamsFile, $customMessages);
        // We get the submitted params if any and store them in self::$requestParams
        self::readRequestParams();
        // If we detect that a Crudity Form has been submitted (a Crudity Form Id has been launched)
        if (self::wasCrudityFormSubmitted()) {
            // We let the adapter search for the instance of the declared Form in config with the submitted id if any
            $submittedForm = self::$adapter->get(self::$requestParams["id"]);
            self::handleFormSubmission($submittedForm);
        }
    }

}
