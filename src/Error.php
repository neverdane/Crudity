<?php
namespace Neverdane\Crudity;

class Error {

    /**
     *
     */
    const FORCED_ERROR      = 100;
    /**
     * Error thrown when a field is required and submitted empty
     */
    const REQUIRED          = 101;
    /**
     * Default error thrown when a submitted input is wrong formatted
     */
    const WRONG_FORMAT      = 102;
    /**
     * Error thrown when a number is under required limit
     */
    const MIN_UNREACHED     = 103;
    /**
     * Error thrown when a number is beyond required limit
     */
    const MAX_EXCEEDED      = 104;
    /**
     * ?
     */
    const OUT_OF_RANGE      = 105;
    /**
     * Error thrown when the submitted value is not a number but a number is expected
     */
    const NOT_A_NUMBER      = 106;
    /**
     * Error thrown when the submitted value is not a string but a string is expected
     */
    const NOT_A_STRING      = 107;
    /**
     * Error thrown when the submitted value does not contain a specific expected character
     */
    const ABSENT_CHAR       = 108;
    /**
     * Error thrown when the submitted value contains a forbidden character
     */
    const FORBIDDEN_CHAR    = 109;
    /**
     * Error thrown when the submitted value contains not enough characters
     */
    const STRING_TOO_SHORT  = 110;
    /**
     * Error thrown when the submitted value contains too many characters
     */
    const STRING_TOO_LONG   = 111;
    /**
     * Error thrown when the submitted value is already stored in the database
     */
    const DUPLICATE_ENTRY    = 112;

    /**
     * The path to the Crudity error massages file
     */
    const MESSAGES_FILE = "config/errors.php";

    protected static $_messages = null;

    /**
     * Initiates error messages and merges them with $customMessages if set
     * @param array $customMessages
     *  Custom messages that override default ones
     */
    public static function initMessages($customMessages = array())
    {
        // We include the default error messages
        $defaultMessages = include(realpath(dirname(__FILE__) . "/../../" . self::MESSAGES_FILE));
        self::$_messages = array_replace_recursive($defaultMessages, $customMessages);
    }

    public static function getMessage($params, $code, $form = null, $field = null)
    {
        $message = self::_getMessage($params, $code, $form, $field);
        $params["field"] = $field;
        return self::_interpretMessage($message, $params);
    }

    protected static function _interpretMessage($message, $params)
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

    protected static function _getMessage($params, $code, $form = null, $field = null)
    {
        $availableParams    = self::_getAvailableParamsByOrder($params, $form->getId(), $field);
        $availableCodes     = self::_getCodesByOrder($code);
        return self::_getMessageByOrder($availableParams, $availableCodes);
    }

    protected static function _getAvailableParamsByOrder($params, $formId, $field = null)
    {
        $availableParams = array();
        if (!is_null($field) && isset(self::$_messages["Forms"][$formId]["Fields"][$field->name])) {
            $availableParams[] = self::$_messages["Forms"][$formId]["Fields"][$field->name];
        }
        if (isset(self::$_messages["Forms"][$formId]["Validators"])) {
            $availableParams[] = self::$_messages["Forms"][$formId]["Validators"];
        }
        if (isset($params["validatorName"]) && isset(self::$_messages["Validators"][$params["validatorName"]])) {
            $availableParams[] = self::$_messages["Validators"][$params["validatorName"]];
        }
        if (isset(self::$_messages["Default"])) {
            $availableParams[] = self::$_messages["Default"];
        }
        return $availableParams;
    }

    protected static function _getCodesByOrder($code)
    {
        return array(self::FORCED_ERROR, $code, self::REQUIRED);
    }

    protected static function _getMessageByCode($availableParams, $code)
    {
        foreach ($availableParams as $availableParam) {
            if (isset($availableParam[$code])) {
                return $availableParam[$code];
            }
        }
        return null;
    }

    protected static function _getMessageByOrder($availableParams, $availableCodes)
    {
        foreach ($availableCodes as $availableCode) {
            $message = self::_getMessageByCode($availableParams, $availableCode);
            if (!is_null($message)) return $message;
        }
        return null;
    }

}
