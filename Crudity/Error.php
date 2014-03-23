<?php

class Crudity_Error {

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
     * The path to the Crudity error massages file
     */
    const MESSAGES_FILE = "config/errors.php";

    protected static $_messages = null;
    
    public static function initMessages($customMessages = array()) {
        $defaultMessages = include(realpath(dirname(__FILE__) . "/" . self::MESSAGES_FILE));
        self::$_messages = array_replace_recursive($defaultMessages, $customMessages);
    }
    
    public static function getMessage($params, $code, $form = null, $field = null) {
        $message = self::_getMessage($params, $code, $form, $field);
        $params["field"] = $field;
        return self::_interpretMessage($message, $params);
    }
    
    protected static function _interpretMessage($message, $params) {
       $message = str_replace("{{name}}", $params["field"]->name, $message);
       //CAUTION ! SECURE $params["value"] FROM XSS !!
       $message = str_replace("{{value}}", $params["value"], $message);
       return $message;
    }
    
    protected static function _getMessage ($params, $code, $form = null, $field = null) {
        $formId = $form->getId();
        if(isset(self::$_messages["Forms"][$formId]["Fields"][$field->name])) {
            $fieldParams = self::$_messages["Forms"][$formId]["Fields"][$field->name];
            if(isset($fieldParams[self::FORCED_ERROR])) {
                return $fieldParams[self::FORCED_ERROR];
            }
        }
        if(isset(self::$_messages["Forms"][$formId]["Validators"])) {
            $formParams = self::$_messages["Forms"][$formId];
            if(isset($formParams[self::FORCED_ERROR])) {
                return $formParams[self::FORCED_ERROR];
            }
        }
        if(isset(self::$_messages["Validators"][$params["validatorName"]])) {
            $validatorParams = self::$_messages["Validators"][$params["validatorName"]];
            if(isset($validatorParams[self::FORCED_ERROR])) {
                return $validatorParams[self::FORCED_ERROR];
            }
        }
        if(isset(self::$_messages["Default"])) {
            $defaultParams = self::$_messages["Default"];
            if(isset($defaultParams[self::FORCED_ERROR])) {
                return $defaultParams[self::FORCED_ERROR];
            }
        }
        
        if(isset($fieldParams[$code])) {
            return $fieldParams[$code];
        }        
        if(isset($formParams[$code])) {
            return $formParams[$code];
        }        
        if(isset($validatorParams[$code])) {
            return $validatorParams[$code];
        }
        if(isset($defaultParams[$code])) {
            return $defaultParams[$code];
        }
        
        if($code === self::REQUIRED) {
            if(isset($fieldParams[self::REQUIRED])) {
                return $fieldParams[self::REQUIRED];
            }        
            if(isset($formParams[self::REQUIRED])) {
                return $formParams[self::REQUIRED];
            }        
            if(isset($validatorParams[self::REQUIRED])) {
                return $validatorParams[self::REQUIRED];
            }
            if(isset($defaultParams[self::REQUIRED])) {
                return $defaultParams[self::REQUIRED];
            }
        } else {
            if(isset($fieldParams[self::REQUIRED])) {
                return $fieldParams[self::REQUIRED];
            }        
            if(isset($formParams[self::REQUIRED])) {
                return $formParams[self::REQUIRED];
            }        
            if(isset($validatorParams[self::REQUIRED])) {
                return $validatorParams[self::REQUIRED];
            }
            if(isset($defaultParams[self::REQUIRED])) {
                return $defaultParams[self::REQUIRED];
            }
        }
        return null;
    }

}
