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
    public function storeForm($id, $form)
    {
        $this->store(self::NAMESPACE_FORM, $id, $form);
    }

    public function getForm($id)
    {
        return $this->get(self::NAMESPACE_FORM, $id);
    }

    private function store($type, $id, $value)
    {
        $this->initSession();
        if (!isset($_SESSION[self::NAMESPACE_CRUDITY][$type])) {
            if (!isset($_SESSION[self::NAMESPACE_CRUDITY])) {
                $_SESSION[self::NAMESPACE_CRUDITY] = array();
            }
            $_SESSION[self::NAMESPACE_CRUDITY][$type] = array();
        }
        $_SESSION[self::NAMESPACE_CRUDITY][$type] = array($id => $value);
    }

    private function get($type, $id)
    {
        $this->initSession();
        $crudityTypeSession = $_SESSION[self::NAMESPACE_CRUDITY][$type];
        if (isset($crudityTypeSession[$id])) {
            return clone $crudityTypeSession[$id];
        }
        return null;
    }

    private function initSession()
    {
        if (!isset($_SESSION)) {
            try {
                session_start();
            } catch (\Exception $e) {

            }
        }
    }

}