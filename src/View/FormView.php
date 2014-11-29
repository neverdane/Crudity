<?php

/*
 * This file is part of the Crudity package.
 *
 * (c) Alban Pommeret <alban@aocreation.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neverdane\Crudity\View;
use Neverdane\Crudity\Form\Config;

/**
 * @package Neverdane\Crudity
 * @author Alban Pommeret <alban@aocreation.com>
 */
class FormView
{
    const PREFIX_DEFAULT = "cr";

    const FIELD_INPUT = "input";
    const FIELD_SELECT = "select";
    const FIELD_TEXTAREA = "textarea";

    const RENDER_TYPE_FILE = 1;
    const RENDER_TYPE_HTML = 2;
    const RENDER_TYPE_OBJECTS = 3;

    public $renderType = self::RENDER_TYPE_HTML;
    public $rendering = null;
    private $config = null;
    private $parser = null;

    /**
     * All the Form elements that Crudity can handle
     * (Different from the handled fields which are more specific)
     * @var array
     */
    public static $managedTagNames = array(
        self::FIELD_INPUT,
        self::FIELD_SELECT,
        self::FIELD_TEXTAREA,
    );

    public static $prefix = self::PREFIX_DEFAULT;

    public function __construct($rendering, $renderType = self::RENDER_TYPE_HTML)
    {
        $this->setConfig(Config::getViewC());
        $this->setRendering($rendering, $renderType);
    }

    public static function setPrefix($prefix = self::PREFIX_DEFAULT)
    {
        self::$prefix = $prefix;
    }

    public function setRendering($rendering, $renderType)
    {
        $this->rendering = $rendering;
        $this->renderType = $renderType;
        return $this;
    }

    public function render()
    {
        $html = $this->rendering;
        return $html;
    }

    public function getParser()
    {
        if(is_null($this->parser)) {
            $this->setParser(new FormParser($this->rendering));
        }
        return $this->parser;
    }

    public function setParser($parser)
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig($config = array())
    {
        $this->config = $config;
        return $this;
    }
}
