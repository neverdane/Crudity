<?php
namespace Neverdane\Crudity;

use Neverdane\Crudity\Form\RequestManager;

abstract class AbstractObserver
{
    public function preValidation(RequestManager $rm)
    {
    }
    public function postValidation(RequestManager $rm)
    {
    }
    public function preFilter(RequestManager $rm)
    {
    }
    public function postFilter(RequestManager $rm)
    {
    }
    public function preProcess(RequestManager $rm)
    {
    }
    public function postProcess(RequestManager $rm)
    {
    }
    public function preCreate(RequestManager $rm)
    {
    }
    public function postCreate(RequestManager $rm)
    {
    }
    public function preUpdate(RequestManager $rm)
    {
    }
    public function postUpdate(RequestManager $rm)
    {
    }
    public function preDelete(RequestManager $rm)
    {
    }
    public function postDelete(RequestManager $rm)
    {
    }
    public function preRead(RequestManager $rm)
    {
    }
    public function postRead(RequestManager $rm)
    {
    }
    public function preFetch(RequestManager $rm)
    {
    }
    public function postFetch(RequestManager $rm)
    {
    }
    public function preSend(RequestManager $rm)
    {
    }
}
