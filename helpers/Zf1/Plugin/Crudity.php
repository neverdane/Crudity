<?php

/**
 * Class Plugin_Crudity
 * This plugin automates the inclusion and the settings of Crudity for Zend Framework 1
 * when registered in the Bootstrap.php file :
 */
class Plugin_Crudity extends Zend_Controller_Plugin_Abstract {

    /**
     * @var null
     * Path to a custom overriding config file for Crudity
     */
    protected $_configFile      = null;
    /**
     * @var array
     * Custom overriding messages shown to the user
     */
    protected $_customMessages  = array();

    /**
     * @param null $configFile
     * @param array $customMessages
     */
    public function __construct($configFile = null, $customMessages = array()) {
        $this->_configFile      = $configFile;
        $this->_customMessages  = $customMessages;
    }

    /**
     * All things that need to be done in order to use Crudity
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        // We add the namespace to the autoloader so Crudity classes don't need to be included
        Zend_Loader_Autoloader::getInstance()->registerNamespace('Crudity_');
        // We set the Adapter used by Crudity to Zend Framework 1
        Crudity_Application::setAdapter(Crudity_Application::ADAPTER_ZF1);
        // We run the Crudity Application with optional overrides
        Crudity_Application::run($this->_configFile, $this->_customMessages);
    }
    
}
