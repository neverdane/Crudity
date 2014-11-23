<?php
namespace Neverdane\Crudity;

class Error
{

    const FORCED_ERROR = 100;
    const REQUIRED = 101;      // Error thrown when a field is required and submitted empty
    const WRONG_FORMAT = 102;      // Default error thrown when a submitted input is wrong formatted
    const MIN_UNREACHED = 103;      // Error thrown when a number is under required limit
    const MAX_EXCEEDED = 104;      // Error thrown when a number is beyond required limit
    const OUT_OF_RANGE = 105;      // ?
    const NOT_A_NUMBER = 106;      // Error thrown when the submitted value is not a number but a number is expected
    const NOT_A_STRING = 107;      // Error thrown when the submitted value is not a string but a string is expected
    const ABSENT_CHAR = 108;      // Error thrown when the submitted value does not contain a specific expected character
    const FORBIDDEN_CHAR = 109;      // Error thrown when the submitted value contains a forbidden character
    const STRING_TOO_SHORT = 110;      // Error thrown when the submitted value contains not enough characters
    const STRING_TOO_LONG = 111;      // Error thrown when the submitted value contains too many characters
    const DUPLICATE_ENTRY = 112;      // Error thrown when the submitted value is already stored in the database

    /**
     * The path to the Crudity error massages file
     */
    const MESSAGES_FILE = "config/errors.php";

    private static $messages = null;
    private static $customMessages = array();

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
     * @param int $code
     * @param array $formMessages
     * @param null|string $fieldName
     * @param null|string $validatorName
     * @param array $placeholders
     * @return null|string
     */
    public static function getMessage($code, $formMessages = array(), $fieldName = null, $validatorName = null, $placeholders = array())
    {
        // First we get the raw message
        $message = self::getRawMessage($code, $formMessages, $fieldName, $validatorName);
        // Then we replace its potential placeholders by their values
        return self::replacePlaceholders($message, $placeholders);
    }

    /**
     * Replaces the potential placeholders of the message and returns it
     * @param string $message
     * @param array $placeholders
     * @return null|string
     */
    private static function replacePlaceholders($message, $placeholders = array())
    {
        if (isset($placeholders["fieldName"]) && !is_null($placeholders["fieldName"])) {
            $message = str_replace("{{name}}", self::escape($placeholders["fieldName"]), $message);
        }
        if (isset($placeholders["value"])) {
            $message = str_replace("{{value}}", self::escape($placeholders["value"]), $message);
        }
        return $message;
    }

    /**
     * Adds a security layer to the given value, preventing XSS
     * @param string $value
     * @return string
     */
    private static function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }


    /**
     * Returns the message matching the most with the given parameters
     * according to the messages of the Error class
     * @param int $code
     * @param array $formMessages
     * @param null|string $fieldName
     * @param null|string $validatorName
     * @return null|string
     */
    private static function getRawMessage($code, $formMessages = array(), $fieldName = null, $validatorName = null)
    {
        // We get all the messages that can match with our error
        // according to the fieldName and the validatorName
        $availableMessages = self::getAvailableMessagesByOrder($formMessages, $fieldName, $validatorName);
        // We get the priority of error codes
        $availableCodes = self::getCodesByOrder($code);
        // Returns the
        return self::getMostRelevantMessage($availableMessages, $availableCodes);
    }

    /**
     * Returns pools of messages ordered by priority
     * that can match with our error according to the fieldName and the validatorName
     * @param array $formMessages
     * @param null|string $fieldName
     * @param null|string $validatorName
     * @return array
     */
    private static function getAvailableMessagesByOrder($formMessages, $fieldName = null, $validatorName = null)
    {
        $availableMessages = array();
        // If a message has been customized for this field on the Form, we get its messages first
        if (!is_null($fieldName) && isset($formMessages["Fields"][$fieldName])) {
            $availableMessages[] = $formMessages["Fields"][$fieldName];
        }
        // Then, if a message has been customized for this validator on the Form, we get its messages
        if (!is_null($validatorName) && isset($formMessages["Validators"][$validatorName])) {
            $availableMessages[] = $formMessages["Validators"][$validatorName];
        }
        // Then, if a message has been set globally for this validator, we get its messages
        if (!is_null($validatorName) && isset(self::$messages["Validators"][$validatorName])) {
            $availableMessages[] = self::$messages["Validators"][$validatorName];
        }
        // Finally, we get all the default messages (wrong format, required...)
        if (isset(self::$messages["Default"])) {
            $availableMessages[] = self::$messages["Default"];
        }
        return $availableMessages;
    }

    /**
     * Returns codes ordered by their priority
     * @param int $code
     * @return array
     */
    private static function getCodesByOrder($code)
    {
        return array(self::FORCED_ERROR, $code, self::REQUIRED);
    }

    /**
     * Traverses the availableMessages
     * in order to found if a message has been set for the given code
     * and returns it if founded
     * @param array $availableMessages
     * @param int $code
     * @return null|string
     */
    private static function getMessageByCode($availableMessages, $code)
    {
        foreach ($availableMessages as $availableParam) {
            if (isset($availableParam[$code])) {
                return $availableParam[$code];
            }
        }
        return null;
    }

    /**
     * Returns the most relevant message
     * @param array $availableMessages
     * @param array $availableCodes
     * @return null|string
     */
    private static function getMostRelevantMessage($availableMessages, $availableCodes)
    {
        foreach ($availableCodes as $availableCode) {
            // First, we check if this code has a message set somewhere
            $message = self::getMessageByCode($availableMessages, $availableCode);
            // If this is the case, we return the matching message
            if (!is_null($message)) return $message;
            // Else, we continue with a lower priority code
        }
        return null;
    }

}
