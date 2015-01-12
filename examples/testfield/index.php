<?php
/**
 * Created by PhpStorm.
 * User: Alban
 * Date: 20/06/14
 * Time: 23:21
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../vendor/autoload.php';

use Neverdane\Crudity\Field\EmailField;
use Neverdane\Crudity\Field\FieldValue;
use Neverdane\Crudity\Form\Response;

$field = new EmailField(array('name' => 'email'));
$field->setValue(new FieldValue('neverdanehotmail.com'));
$field->setValue(new FieldValue('neverdane@hotmail.com'), 1);
$field->setValue(new FieldValue('neverdanevdhotmail.com'), 2);
$response = new Response();
$field->validate($response);
var_dump($response->getResponse());

$value = new FieldValue('neverdane@hotmail.com');
$value->validate(false, array(
    new \Neverdane\Crudity\Validator\EmailValidator()
));
var_dump($value->getStatus());