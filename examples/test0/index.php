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
require "TestFormObserver.php";

use Neverdane\Crudity\Crudity;
use Neverdane\Crudity\Db;
use Neverdane\Crudity\Field;

$registry = new \Neverdane\Crudity\Registry();
$pdo = new PDO('mysql:host=localhost;dbname=crudity', 'root', '');
Db\Db::registerAdapter('pdo', new Db\Layer\PdoAdapter($pdo));
\Neverdane\Crudity\Listener::listen();

$form = Crudity::createFromFile("form.php", 'user');
$form->setErrorMessages(array(
    "Fields" => array(
        "first_name" => array(
            \Neverdane\Crudity\Error::REQUIRED => "{{name}} ne semble pas être renseigné."
        )
    )
));
$form->addObserver(new TestFormObserver());
/*$form->getEntity('user')->setDependencies(array(
    'contact_id' => 'contact'
));*/

$contactField = new Field\TextField(array('name' => 'contact_id', 'join' => 'contact'));
$form->getEntity('user')->setField($contactField);


/*$contactEntity->setDefaultValues(array(
    'country' => 'France'
))*/;

//$form->setEntity('user');

$registry->storeForm($form);

?>
<html>
<head>
    <link rel="stylesheet" href="/public/crudity/css/crudity.css">
</head>
<body>
<?php echo $form->getView()->render(); ?>
<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
<script src="/public/crudity/js/crudity.js"></script>
<script>
    $().ready(function() {
        $('form').crSetCreate();
    });
</script>
</body>
</html>
