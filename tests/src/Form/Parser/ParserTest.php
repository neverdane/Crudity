<?php
namespace Neverdane\Crudity\Test;

use Neverdane\Crudity\Form\Parser\Parser;
use Neverdane\Crudity\Form\Parser\PhpQueryAdapter;

class ParserTest extends \PHPUnit_Framework_TestCase
{

    public function testParserSetsTheHtmlOnConstruct()
    {
        $parser = new Parser('HTML');
        $this->assertEquals('HTML', $parser->getHtml());
    }

    public function testParserInstanciatesFieldManagerOnConstruct()
    {
        $parser = new Parser('HTML');
        $this->assertInstanceOf('Neverdane\Crudity\Field\FieldHandler', $parser->getFieldHandler());
    }

    public function testGetAdapterSetsPhpQueryAdapterWhenNull()
    {
        $parser = new Parser('HTML');
        $this->assertInstanceOf('Neverdane\Crudity\Form\Parser\PhpQueryAdapter', $parser->getAdapter());
    }

    public function testSetAdapterSetsHtmlToAdapter()
    {
        $parser = new Parser('HTML');
        $parser->setAdapter(new PhpQueryAdapter());
        $this->assertEquals('HTML', $parser->getAdapter()->getHtml());
    }

}