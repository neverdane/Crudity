<?php
namespace Neverdane\Crudity\Adapter;

class Zf1Adapter extends AbstractAdapter {

    public function manageAutoload() {
        
    }

    public function manageSession() {
        
    }

    public function getRequestParams() {
        $fc = Zend_Controller_Front::getInstance();
        $params = $fc->getRequest()->getPost();
        return $params;
    }

    public static function store($id, $form) {
        $cruditySession = new Zend_Session_Namespace("Crudity");
        $tmpForms = array();
        if (!isset($cruditySession->forms)) {
            $cruditySession->forms = array();
        } else {
            $tmpForms = unserialize($cruditySession->forms);
        }
        $tmpForms[$id] = $form;

        $cruditySession->forms = serialize($tmpForms);
    }

    public static function get($id) {
        $cruditySession = new Zend_Session_Namespace("Crudity");
        $forms = unserialize($cruditySession->forms);
        if (isset($forms[$id])) {
            return $forms[$id];
        }
        return null;
    }

    public function render($partial) {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $viewRenderer->view;
        echo $view->render($partial);
    }

    public function create($modelName, $params) {
        $status = true;
        $code   = null;
        $rowId  = null;
        $model = new $modelName();
        $row = $model->createRow();
        foreach ($params as $param => $value) {
            $row->$param = $value;
        }
        try {
            return $row->save();
        } catch (Zend_Db_Exception $e) {
            $code = $e->getCode();
            switch($code) {
                case 23000 :
                    $status = false;
                    $code   = Crudity_Error::DUPLICATE_ENTRY;
            }
        }
        return array(
            "status"    => $status,
            "code"      => $code,
            "rowId"     => $rowId
        );
    }

    public function update($modelName, $rowId, $params) {
        $model = new $modelName();
        $row = $model->find($rowId)->current();
        foreach ($params as $param => $value) {
            $row->$param = $value;
        }
        $row->save();
    }

    public function delete($modelName, $rowId) {
        $model = new $modelName();
        $row = $model->find($rowId)->current();
        $row->delete();
    }

    public function read($modelName, $fields, $rowId = null) {
        $model = new $modelName();
        $tableName = $model->info($model::NAME);
        $select = $model->select();
        $fieldsList = array();
        foreach ($fields as $field) {
            if (!is_null($rowId)) {
                $fieldsList[] = $field->column;
            } else {
                $fieldsList[$field->column] = new Zend_Db_Expr("DEFAULT(" . $field->column . ")");
            }
        }
        $select->from($tableName, $fieldsList);
        if (!is_null($rowId)) {
            $select->where("id = ?", $rowId);
        }
        $row = $model->fetchRow($select);
        return (is_null($row)) ? null : $row->toArray();
    }

}
