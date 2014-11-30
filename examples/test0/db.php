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

use Neverdane\Crudity\Crudity;

$db = new \Neverdane\Crudity\Db\Adapter\PdoAdapter();
$pdo = new PDO('mysql:host=localhost;dbname=crudity', 'root', '');
$db->setConnection($pdo);
var_dump($db->createRow("user", array(
    "first_name" => "Alban",
    "last_name" => "Pommeret",
    "email" => "neverdane@hotmail.com",
    "age" => 27,
    "address" => null
)));