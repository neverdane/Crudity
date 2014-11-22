<?php
namespace Neverdane\Crudity;

use Neverdane\Crudity\Form\Form;

abstract class AbstractObserver
{
    public function preValidation(Form $form)
    {
    }
    public function postValidation(Form $form)
    {
    }
    public function preFilter(Form $form)
    {
    }
    public function postFilter(Form $form)
    {
    }
    public function preSend(Form $form)
    {
    }
}
