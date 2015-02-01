<?php
namespace Neverdane\Crudity;

use Neverdane\Crudity\Form\Request;
use Neverdane\Crudity\Form\RequestManager;

class Listener
{

    /**
     * Listens to the potential Crudity data sent and triggers the requested action if any
     *
     * @param null|Registry $registry
     * @return $this
     */
    public function listen($registry = null)
    {
        $registry = (!is_null($registry)) ? $registry : new Registry();
        // We get the submitted params if any
        $requestParams = $this->getRequestParams();
        // If we detect that a Crudity Form has been submitted (a crudity Form Id has been launched)
        if ($this->wasCrudityFormSubmitted($requestParams)) {
            // We let the adapter search for the instance of the declared Form in config with the submitted id if any
            $submittedForm = $registry->getForm($requestParams["id"]);
            // If a Form was found
            if (!is_null($submittedForm)) {
                $requestManager = new RequestManager(new Request($requestParams), $submittedForm);
                $workflow = new Workflow($requestManager);
                $workflow->start();
            } else {
                // If no declared Form has been found with this id
            }
        }
        return $this;
    }

    /**
     * Extracts the Crudity data from the given request
     *
     * We get the potentially submitted params and organize and return them as an array :
     *  ["id"]      => (string) The Crudity Form Id
     *  ["action"]  => (string) The action to perform with submitted params
     *  ["row_id"]  => (string) The optional Row Id to affect or read if action is read, update or delete
     *  ["params"]  => (array)  All other params submitted
     *  ["rows"]    => (array) The rows to fetch or affect
     * @param null|array $request
     * @return array
     */
    private function getRequestParams($request = null)
    {
        $userParams = (!is_null($request)) ? $request : $_POST;
        // We initialize the params
        $requestParams = array(
            'id' => null,
            'action' => null,
            'row_id' => null,
            'params' => array(),
            'rows'   => array()
        );
        foreach ($userParams as $paramName => $paramValue) {
            switch ($paramName) {
                case 'crudity_form_action':
                    $requestParams['action'] = $paramValue;
                    continue;
                case 'crudity_form_row_id':
                    $requestParams['row_id'] = $paramValue;
                    continue;
                case 'crudity_form_id':
                    $requestParams['id'] = $paramValue;
                    continue;
                case 'crudity_form_rows':
                    $requestParams['rows'] = $paramValue;
                    continue;
                default:
                    $requestParams['params'][$paramName] = $paramValue;
                    break;
            }
        }
        return $requestParams;
    }

    /**
     * Detects if a Crudity Form has been submitted (a Crudity Form Id has been sent)
     * @param array $requestParams
     * @return bool
     */
    private function wasCrudityFormSubmitted($requestParams)
    {
        return !is_null($requestParams["id"]);
    }

}
