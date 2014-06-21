<?php
namespace Neverdane\Crudity\Form;
use Neverdane\Crudity\Crudity;
use Neverdane\Crudity\Error;
use Neverdane\Crudity\Exception\Exception;
use phpQuery;

/**
 * Form class stores the params of the form
 */
class Form
{

    /**
     * The status on Crudity execution success
     */
    const STATUS_SUCCESS = 1;
    /**
     * The status on Crudity execution failure
     */
    const STATUS_FAILURE = 0;

    const ACTION_CREATE = "create";
    const ACTION_READ = "read";
    const ACTION_UPDATE = "update";
    const ACTION_DELETE = "delete";

    /**
     * The original HTML passed to the form
     * Converted in a phpQueryObject
     * @var phpQueryObject
     */
    protected $_doc;

    /**
     * The phpQueryObject form
     * @var phpQueryObject
     */
    protected $_form = null;

    /**
     * The path to the form's HTML script
     * @var string
     */
    protected $_partial = "";

    /**
     * The array which stores the fields of the Form
     * @var array
     */
    protected $_fields = array();

    /**
     * Raw params GET or POST from the server
     * @var array
     */
    protected $_dirtyParams = array();

    /**
     * Cleaned dirtyParams
     * @var array
     */
    protected $_cleanParams = array();
    public $_id = "";
    public $crudityParams = array();

    /**
     * All errors get during after the treatment of user input
     * @var array
     */
    protected $_errors = array();

    /**
     * The status of the user input treatment and the CRUD action. Can be :
     * - self::STATUS_SUCCESS
     * - self::STATUS_FAILURE
     * @var int
     */
    protected $_status = self::STATUS_SUCCESS;

    /**
     * Creates an instance of Form
     * Gets the script's HTML view to the form
     * @param string $partial
     */
    public function __construct($partial)
    {
        $this->_partial = $partial;
        // We initialize the DOMDocument
        $this->_initDoc();
        // We set the Crudity params for this specific Form instance
        $this->setCrudityParams();
        $this->populate(FormParser::parse($this->_form));
    }

    /**
     * Initializes the DOMDocument instance from $this->_partial
     */
    protected function _initDoc()
    {
        // We store the HTML content to a variable
        ob_start();
        Crudity::$adapter->render($this->_partial);
        $html = ob_get_contents();
        ob_end_clean();
        // To simplify the DOM interaction, we convert the HTML to phpQueryObject (jQuery's PHP version)
        $this->_doc = phpQuery::newDocument($html);
        // We store the form phpQuery Element
        $this->_form = $this->_doc->find("form");
    }

    /**
     * Stores the result of analysis ($params) in the Form
     * @param array $params
     */
    public function populate($params)
    {
        $this->_id = $params["id"];
        $this->_fields = $params["fields"];
    }

    /**
     * Returns the cleaned HTML form to display at the user
     * @return phpQueryObject
     */
    public function render()
    {
        $this->_clean();
        $this->_customize();
        return $this->_doc;
    }

    /**
     * Cleans the HTML in order to
     * hide the unneeded HTML attributes for the user
     * (especially Crudity attributes)
     */
    protected function _clean()
    {
        // We prevent the browser to block the Crudity validation (HTML5 specific)
        $this->_form->attr("novalidate", true)
            ->addClass(Crudity::$prefix . "-form");
    }

    /**
     * Cleans the HTML in order to
     * hide the unneeded HTML attributes for the user
     * (especially Crudity attributes)
     */
    protected function _customize()
    {
        $keepedParams = array();
        $keepedParams["highlightGuilt"] = $this->params["highlightGuilt"];
        $keepedParams["errors"] = $this->params["errors"];
        $keepedParams["messages"] = $this->params["messages"];
        $this->_form->attr("data-crudity-params", json_encode($keepedParams));
    }

    /**
     * Launches the validation of the $params
     * If validated, launches the filtering
     * If wanted, ends with a CRUD action depending on the Form type
     * Returns an array :
     *  "status"    => (int)    Status of the validation,
     *  "message"   => (string) Message returned by the validation
     *  "extra"     => ()
     * @param string $action
     * @param array $params
     * @param string $rowId
     * @return array|boolean
     */
    protected function _manage($action, $params, $rowId = null)
    {
        $this->_dirtyParams = $params;
        $params = $this->transform($params);
        $return = $this->validate($params);
        $return["extra"] = array();
        if ($return["status"] === self::STATUS_SUCCESS) {
            $this->_cleanParams = $this->filter($params);
            $formParams = Crudity::$params["Forms"][$this->_id];

            if (isset($formParams["noDuplicates"])) {
                /*$model = new $formParams["model"]();
                $*/
            }

            $return["extra"] = $this->_succeed($action, $rowId, $return);
        }
        return $return;
    }

    /**
     * Validates the given $params for the Form
     * Returns an array :
     *  "status"    => (int) Status of the validation,
     *  "errors"   => (array) Messages returned by the validation
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function validate($params = null)
    {
        //If no $params set, validates the dirtyParams of the Form
        if (is_null($params)) {
            $params = $this->_dirtyParams;
        }
        $result = array(
            "status" => self::STATUS_SUCCESS
        );

        // We iterate on each field declared on this Form
        foreach ($this->_fields as $field) {
            // We initialize the status of the current field validation to success and no error code
            $status = self::STATUS_SUCCESS;
            $code = null;
            $validatorName = null;
            $userInput = (isset($params[$field->name])) ? $params[$field->name] : null;
            // If this expected field was not submitted by the user (probably hack attempt), an exception is thrown
            if (is_null($userInput)) {
                throw new Exception("The field " . $field->name . " was expected but not submitted");
            }
            // If this expected field is required
            if ($field->required) {
                // If empty, an error is added to the error stack
                if ($userInput === "" || is_null($userInput)) {
                    $status = self::STATUS_FAILURE;
                    $code = Error::REQUIRED;
                }
            }
            // If no error was detected for this field previously and a validation is needed
            if ($status !== self::STATUS_FAILURE && !is_null($field->validators)) {
                foreach($field->validators as $validator) {
                    // We get the validator class suffix
                    $validatorName = ucfirst($validator);
                    // We get the validator class name
                    $validator = "Crudity_Validator_" . $validatorName;
                    $validation = $validator::validate($userInput, $this);
                    if ($validation["success"] !== true) {
                        $status = self::STATUS_FAILURE;
                        $code = $validation["code"];
                    }
                }
            }
            if ($status === self::STATUS_FAILURE) {
                $messagesParams = array(
                    "value" => $userInput,
                    "validatorName" => $validatorName
                );
                $result["status"] = self::STATUS_FAILURE;
                $this->addError($code, $messagesParams, $field);
            }
            if ($this->params["errors"]["showAll"] !== true && $status === self::STATUS_FAILURE) {
                break;
            }
        }
        return $result;
    }

    public function getId()
    {
        return $this->_id;
    }

    /**
     * Validates the given $params for the Form
     * Returns an array containing the filtered params
     * @param array $params
     * @return array
     */
    public function filter($params = null)
    {
        //If no $params set, filters the dirtyParams of the Form
        if (is_null($params)) {
            $params = $this->_dirtyParams;
        }
        $cleanParams = array();
        foreach ($params as $paramName => $paramValue) {
            $cleanParams[$paramName] = $paramValue;
        }
        return $cleanParams;
    }

    public function transform($params = null)
    {
        //If no $params set, filters the dirtyParams of the Form
        if (is_null($params)) {
            $params = $this->_dirtyParams;
        }
        $cleanParams = array();

        foreach ($this->_fields as $field) {
            if (isset($params[$field->name])) {
                $cleanParams[$field->name] = $field->transform($params[$field->name]);
            } else {
                $cleanParams[$field->name] = $field->transform(null);
            }
        }

        return $cleanParams;
    }

    /**
     * TODO Creates the crud function
     * Currently just tests it directly in JSON without db adapter
     */
    protected function _succeed($action, $rowId = null, &$result = null)
    {
        $formParams = Crudity::$params["Forms"][$this->_id];
        if (isset($formParams["model"])) {
            if ($action === self::ACTION_UPDATE) {
                $id = Crudity::$adapter->update($formParams["model"], $rowId, $this->_cleanParams);
            } elseif ($action === self::ACTION_CREATE) {
                $result = Crudity::$adapter->create($formParams["model"], $this->_cleanParams);
                $id = $result["rowId"];
                if($result["status"] === false) {
                    $this->addError(Error::DUPLICATE_ENTRY);
                    return false;
                }
                if (isset($formParams["onSuccess"])) {
                    $model = new $formParams["model"]();
                    $model->$formParams["onSuccess"]["method"]($id);
                }
            }
        }
    }

    /**
     * We parse and store the Crudity Params and override them by the Form specific params if any into $this->params
     * @return Form
     */
    public function setCrudityParams()
    {
        // We get the previously stored Crudity::$params containing our wanted
        $this->params = Crudity::$params;
        // If our Form is customized in these params, we override the params by the customized one
        if (isset($this->params["Forms"][$this->_id]) && count($this->params) > 0) {
            $formParams = $this->params["Forms"][$this->_id];
            // The loop overriding the default params
            foreach ($this->params as $param => $value) {
                // Foreach default params except "Forms" (pretty logical)
                if ($param !== "Forms" && isset($formParams[$param])) {
                    // We override errors (We override each param specifically rather than overriding the entire array)
                    if ($param === "errors" && isset($formParams[$param])) {
                        $this->params["errors"] = array_merge($this->params["errors"], $formParams[$param]);
                        break;
                    }
                    // We override each param
                    $this->params[$param] = $formParams[$param];
                }
            }
        }
        return $this;
    }

    /**
     * Creates a row in the table of the Model associated to the Form
     * Returns
     * @param $params
     * @return array|bool
     */
    public function create($params)
    {
        return $this->_manage(self::ACTION_CREATE, $params);
    }

    public function read($rowId)
    {
        $formParams = Crudity::$params["Forms"][$this->_id];
        $fields = array();
        if (isset($formParams["model"])) {
            $fields = Crudity::$adapter->read($formParams["model"], $this->_fields, $rowId);
        }
        return $fields;
    }

    public function update($rowId, $params)
    {
        return $this->_manage(self::ACTION_UPDATE, $params, $rowId);
    }

    public function delete($rowId)
    {
        $formParams = Crudity::$params["Forms"][$this->_id];
        if (isset($formParams["model"])) {
            Crudity::$adapter->delete($formParams["model"], $rowId);
        }
    }

    public function addError($code, $params = array(), $guiltField = null) {
        $error = array(
            "code" => $code,
            "message" => Error::getMessage($params, $code, $this, $guiltField)
        );
        if(!is_null($guiltField)) {
            $error["guilt"] = $guiltField->name;
        }
        $this->_errors[] = $error;
    }

    public function getErrors() {
        return $this->_errors;
    }

}
