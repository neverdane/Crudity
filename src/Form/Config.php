<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\Form;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class Config
{

    const PARAM_ERROR_STOPPED_AT_FIRST = "errorStoppedAtFirst";
    const PARAM_ERROR_GROUPED = "errorGrouped";
    const PARAM_ERROR_HIGHLIGHTED = "errorHighlighted";

    const TYPE_VIEW = "view";

    private static $defaultConfig = null;

    private $params = array();

    public function __construct()
    {
        $this->params = self::getDefault();
    }

    public function setParam($param, $value)
    {
        $this->params[$param] = $value;
        return $this;
    }

    public function getParam($param)
    {
        return $this->params[$param];
    }

    public function getConfig($type = null)
    {
        switch($type) {
            case self::TYPE_VIEW:
                return self::getConfigFromParams(self::getViewParams(), $this->params);
        }
        return $this->params;
    }

    public static function getDefault($type = null)
    {
        if(is_null(self::$defaultConfig)) {
            self::$defaultConfig = array(
                self::PARAM_ERROR_GROUPED => false,
                self::PARAM_ERROR_HIGHLIGHTED => true,
                self::PARAM_ERROR_STOPPED_AT_FIRST => false
            );
        }
        return self::getConfigByTypeAndContext($type);
    }

    private static function getConfigByTypeAndContext($type, $fullConfig = array())
    {
        if(is_null($fullConfig)) {
            $fullConfig = self::$defaultConfig;
        }
        switch($type) {
            case self::TYPE_VIEW:
                return self::getConfigFromParams(self::getViewParams(), $fullConfig);
        }
        return $fullConfig;
    }

    private static function getConfigFromParams($params, $fullConfig = null)
    {
        if(is_null($fullConfig)) {
            $fullConfig = self::getDefault();
        }
        $config = array();
        foreach ($params as $param) {
            $config[$param] = $fullConfig[$param];
        }
        return $config;
    }

    public static function setDefault($config)
    {
        self::$defaultConfig = array_merge(self::$defaultConfig, $config);
        return self::$defaultConfig;
    }

    public static function getViewParams()
    {
        return array(
            self::PARAM_ERROR_GROUPED,
            self::PARAM_ERROR_HIGHLIGHTED
        );
    }

}
