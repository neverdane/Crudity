<?php
namespace Neverdane\Crudity;

use Neverdane\Crudity\Field\FieldInterface;
use Neverdane\Crudity\Form\Form;

class Error {

    const FORCED_ERROR      = 100;
    const REQUIRED          = 101;      // Error thrown when a field is required and submitted empty
    const WRONG_FORMAT      = 102;      // Default error thrown when a submitted input is wrong formatted
    const MIN_UNREACHED     = 103;      // Error thrown when a number is under required limit
    const MAX_EXCEEDED      = 104;      // Error thrown when a number is beyond required limit
    const OUT_OF_RANGE      = 105;      // ?
    const NOT_A_NUMBER      = 106;      // Error thrown when the submitted value is not a number but a number is expected
    const NOT_A_STRING      = 107;      // Error thrown when the submitted value is not a string but a string is expected
    const ABSENT_CHAR       = 108;      // Error thrown when the submitted value does not contain a specific expected character
    const FORBIDDEN_CHAR    = 109;      // Error thrown when the submitted value contains a forbidden character
    const STRING_TOO_SHORT  = 110;      // Error thrown when the submitted value contains not enough characters
    const STRING_TOO_LONG   = 111;      // Error thrown when the submitted value contains too many characters
    const DUPLICATE_ENTRY   = 112;      // Error thrown when the submitted value is already stored in the database

    /**
     * The path to the Crudity error massages file
     */
    const MESSAGES_FILE = "config/errors.php";

    private static $messages = null;
    private static $customMessages = null;

    /**
     * Initiates error messages and merges them with customMessages if set
     */
    public static function initialize()
    {
        // We get the default error messages
        $defaultMessages = include(realpath(dirname(__FILE__) . "/" . self::MESSAGES_FILE));
        // And we merge them with the optional custom ones
        self::$messages = array_replace_recursive($defaultMessages, self::$customMessages);
    }

    /**
     * @param array $customMessages
     */
    public static function setCustomMessages($customMessages)
    {
        self::$customMessages = $customMessages;
    }

    /**
     * Returns the message matching the most with the given parameters
     * according to the messages of the Error class
     * @param array $params
     * @param int $code
     * @param Form $form
     * @param null | FieldInterface $field
     * @return string
     */
    public static function getMessage($params, $code, $form = null, $field = null)
    {
        // First we get the raw message
        $message = self::getRawMessage($params, $code, $form, $field);
        // We add the field to the params to simplify the signature
        $params["field"] = $field;
        // Then we replace its potential placeholders by their values
        return self::interpretMessage($message, $params);
    }

    private static function interpretMessage($message, $params)
    {
        if(isset($params["field"]->name)) {
            $message = str_replace("{{name}}", $params["field"]->name, $message);
        }
        //CAUTION ! SECURE $params["value"] FROM XSS !!
        if(isset($params["value"])) {
            $message = str_replace("{{value}}", $params["value"], $message);
        }
        return $message;
    }

    /**
     * Returns the message matching the most with the given parameters
     * according to the messages of the Error class
     * @param array $params
     * @param int $code
     * @param Form $form
     * @param null | FieldInterface $field
     * @return null | string
     */
    private static function getRawMessage($params, $code, $form, $field = null)
    {
        // We get all the messages that can match with our error
        $availableParams    = self::getAvailableParamsByOrder($params, $form, $field);
        $availableCodes     = self::getCodesByOrder($code);
        return self::getMessageByOrder($availableParams, $availableCodes);
    }

    private static function getAvailableParamsByOrder($params, $formId, $field = null)
    {
        $availableParams = array();
        if (!is_null($field) && isset(self::$messages["Forms"][$formId]["Fields"][$field->name])) {
            $availableParams[] = self::$messages["Forms"][$formId]["Fields"][$field->name];
        }
        if (isset(self::$messages["Forms"][$formId]["Validators"])) {
            $availableParams[] = self::$messages["Forms"][$formId]["Validators"];
        }
        if (isset($params["validatorName"]) && isset(self::$messages["Validators"][$params["validatorName"]])) {
            $availableParams[] = self::$messages["Validators"][$params["validatorName"]];
        }
        if (isset(self::$messages["Default"])) {
            $availableParams[] = self::$messages["Default"];
        }
        return $availableParams;
    }

    private static function getCodesByOrder($code)
    {
        return array(self::FORCED_ERROR, $code, self::REQUIRED);
    }

    private static function getMessageByCode($availableParams, $code)
    {
        foreach ($availableParams as $availableParam) {
            if (isset($availableParam[$code])) {
                return $availableParam[$code];
            }
        }
        return null;
    }

    private static function getMessageByOrder($availableParams, $availableCodes)
    {
        foreach ($availableCodes as $availableCode) {
            $message = self::getMessageByCode($availableParams, $availableCode);
            if (!is_null($message)) return $message;
        }
        return null;
    }

}
