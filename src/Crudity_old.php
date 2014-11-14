<?php
namespace Neverdane\Crudity;

/**
 * This library is used in order to parse and interact easily with the DOM
 */
use Neverdane\Crudity\Exception\Exception;
use Neverdane\Crudity\Form\Form;
use Neverdane\Crudity\Adapter\AbstractAdapter;

//require_once __DIR__ . "/../../libs/phpQuery/phpQuery.php";

class Crudityold
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

    /**
     * Calls the Form render function and echoes it
     * @param string $partial
     */
    public static function render($partial)
    {
        // We create a _Form instance from parsing $partial
        $form = new Form($partial);
        // We store the Form instance in session so we can retrieve it on submit
        self::$adapter->store($form->id, $form);
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
        // We initialize the Crudity environment (config files, session...)
        self::initialize($customParamsFile, $customMessages);
        Listener::listen();
    }

}
