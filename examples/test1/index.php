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

//Crudity::setObservers(array(new TestFormObserver()));
Crudity::run("config.json");
?>
    <script src="/assets/jquery/dist/jquery.min.js"></script>
    <script src="/public/crudity/js/crudity.js"></script>
    <script src="/public/crudity/js/getHiddenDimensions.js"></script>
    <script src="/public/crudity/js/riplace.js"></script>
    <script src="/public/crudity/js/selectly.js"></script>
    <link rel="stylesheet" href="/public/crudity/css/crudity.css">
<?php
Crudity::render("form.php");
