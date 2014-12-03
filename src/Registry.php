<?php
namespace Neverdane\Crudity;

class Registry
{

    const NAMESPACE_CRUDITY = "Crudity";
    const NAMESPACE_FORM = "Forms";
    const NAMESPACE_CONNECTION = "Connections";

    public static function storeForm($id, $form)
    {
        self::store(self::NAMESPACE_FORM, $id, $form);
    }

    public static function storeConnection($id, $connection)
    {
        self::storeConnection(self::NAMESPACE_CONNECTION, $id, $connection);
    }

    public static function getForm($id)
    {
        return self::get(self::NAMESPACE_FORM, $id);
    }

    public static function getConnection($id)
    {
        return self::get(self::NAMESPACE_CONNECTION, $id);
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
            return $crudityTypeSession[$id];
        }
        return null;
    }

    private static function initSession()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

}