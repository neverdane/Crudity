<?php
namespace Neverdane\Crudity;

/**
 * This library is used in order to parse and interact easily with the DOM
 */
use Neverdane\Crudity\Exception\Exception;
use Neverdane\Crudity\Form\Form;
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
     * @var AbstractAdapter
     * The adapter used by Crudity
     */
    public static $adapter = null;

    /**
     * Calls the Form render function and echoes it
     * @param string $partial
     */
    public static function render($partial = null)
    {
        // We create a _Form instance from parsing $partial
        $form = new Form();
        if(!is_null($partial)) {
            $form->setHtmlInAndOut($partial);
        }
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
        Config::initialize($customParamsFile, $customMessages);
        Listener::listen();
    }

}
