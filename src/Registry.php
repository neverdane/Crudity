<?php
namespace Neverdane\Crudity;

use Neverdane\Crudity\Form\Form;

class Registry
{

    const NAMESPACE_CRUDITY = "Crudity";
    const NAMESPACE_FORM = "Forms";

    /**
     * @param $id
     * @param Form $form
     */
    public static function storeForm($id, $form)
    {
        self::store(self::NAMESPACE_FORM, $id, $form);
    }

    public static function getForm($id)
    {
        return self::get(self::NAMESPACE_FORM, $id);
    }

    public static function store($type, $id, $value)
    {
        self::initSession();
        if (!isset($_SESSION[self::NAMESPACE_CRUDITY][$type])) {
            if (!isset($_SESSION[self::NAMESPACE_CRUDITY])) {
                $_SESSION[self::NAMESPACE_CRUDITY] = array();
            }
            $_SESSION[self::NAMESPACE_CRUDITY][$type] = array();
        }
        $_SESSION[self::NAMESPACE_CRUDITY][$type] = array($id => $value);
    }

    public static function get($type, $id)
    {
        self::initSession();
        $crudityTypeSession = $_SESSION[self::NAMESPACE_CRUDITY][$type];
        if (isset($crudityTypeSession[$id])) {
            return clone $crudityTypeSession[$id];
        }
        return null;
    }

    private static function initSession()
    {
        if (!isset($_SESSION)) {
            try {
                session_start();
            } catch(\Exception $e) {

            }
        }
    }

}