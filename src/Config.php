<?php
namespace Neverdane\Crudity;

/**
 * This library is used in order to parse and interact easily with the DOM
 */
use Neverdane\Crudity\Exception\Exception;
use Neverdane\Crudity\Form\Form;
use Neverdane\Crudity\Adapter\AbstractAdapter;

//require_once __DIR__ . "/../../libs/phpQuery/phpQuery.php";

class Config
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
    public static function initialize($customParamsFile = null, array $customMessages = array())
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

    public static function getCustomMessages() {
        return self::$customMessages;
    }

    private static function initializeParams($customParamsFile)
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

}
