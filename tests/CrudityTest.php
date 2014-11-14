<?php
namespace Neverdane\Crudity\Test;

use Neverdane\Crudity;

class CrudityTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        require '../vendor/autoload.php';
    }

    public function testCreateForm()
    {
        $form = Crudity\Crudity::createFromHtml("", false);
        $this->assertInstanceOf('Neverdane\Crudity\Form', $form);
    }

}