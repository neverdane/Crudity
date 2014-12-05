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
    public function preCreate(Form $form)
    {
    }
    public function postCreate(Form $form)
    {
    }
    public function preSend(Form $form)
    {
    }
}
