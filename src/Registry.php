<?php
namespace Neverdane\Crudity;

class Registry
{

    const NAMESPACE_CRUDITY = "Crudity";

    public static function storeForm($id, $form)
    {
        self::store("form", $id, $form);
    }

    public static function storeConnection($id, $form)
    {
        self::storeConnection("form", $id, $form);
    }

    private static function store($id, $form)
    {
        self::initSession();
        if (!isset($_SESSION[self::NAMESPACE_CRUDITY])) {
            $_SESSION[self::NAMESPACE_CRUDITY] = array();
        }
        $_SESSION[self::NAMESPACE_CRUDITY] = serialize(array($id => $form));
    }

    public static function get($id)
    {
        self::initSession();
        $cruditySession = unserialize($_SESSION[self::NAMESPACE_CRUDITY]);
        if (isset($cruditySession[$id])) {
            return $cruditySession[$id];
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