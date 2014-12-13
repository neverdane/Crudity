<?php
namespace Neverdane\Crudity\Test;

use Neverdane\Crudity\Form\Parser\Parser;
use Neverdane\Crudity\Form\Parser\PhpQueryAdapter;
use Neverdane\Crudity\Form\View;

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

    public function testShouldReturnTheFormId()
    {
        $html = '<form id="myId"></form>';
        $expectedId = 'myId';

        $adapter = $this->getMock('\Neverdane\Crudity\Form\Parser\NullAdapter');
        $adapter->expects($this->once())
            ->method('setHtml')
            ->with($this->equalTo($html));
        $adapter->expects($this->once())
            ->method('getFormId')
            ->will($this->returnValue($expectedId));

        $parser = new Parser($html);
        $parser->setAdapter($adapter);

        $this->assertEquals($expectedId, $parser->getId());
    }

    public function testShouldNotEvaluateTwiceTheFormId()
    {
        $adapter = $this->getMock('\Neverdane\Crudity\Form\Parser\NullAdapter');
        $adapter->expects($this->once())
            ->method('getFormId')
            ->will($this->returnValue(''));
        $parser = new Parser('');
        $parser->setAdapter($adapter);
        $parser->getId();
        $parser->getId();
    }

    public function testShouldReturnFieldsOccurrences()
    {
        $html = '<form id="myId"><input name="first_name"></form>';

        $expectedOccurrences = array('<input name="first_name">');

        $adapter = $this->getMock('\Neverdane\Crudity\Form\Parser\NullAdapter');
        $adapter->expects($this->once())
            ->method('setHtml')
            ->with($this->equalTo($html));
        $adapter->expects($this->once())
            ->method('getFieldsOccurrences')
            ->with($this->equalTo(View::$managedTagNames))
            ->will($this->returnValue($expectedOccurrences));
        $adapter->expects($this->any())
            ->method('isFieldRelevant')
            ->with($this->equalTo('<input name="first_name">'))
            ->will($this->returnValue(true));

        $parser = new Parser($html);
        $parser->setAdapter($adapter);

        $this->assertEquals($expectedOccurrences, $parser->getFieldsOccurrences());
    }

    public function testShouldReturnFilteredFieldsOccurrences()
    {
        $html = '<form id="myId"><input type="submit"></form>';

        $expectedOccurrences = array('<input type="submit">');

        $adapter = $this->getMock('\Neverdane\Crudity\Form\Parser\NullAdapter');
        $adapter->expects($this->once())
            ->method('setHtml')
            ->with($this->equalTo($html));
        $adapter->expects($this->once())
            ->method('getFieldsOccurrences')
            ->with($this->equalTo(View::$managedTagNames))
            ->will($this->returnValue($expectedOccurrences));
        $adapter->expects($this->any())
            ->method('isFieldRelevant')
            ->with($this->equalTo('<input type="submit">'))
            ->will($this->returnValue(false));

        $parser = new Parser($html);
        $parser->setAdapter($adapter);

        $this->assertEquals(array(), $parser->getFieldsOccurrences());
    }

    public function testShouldReturnFields()
    {
        $html = '<form id="myId"><input name="first_name"></form>';

        $field = $this->getMock('\Neverdane\Crudity\Field\AbstractField');

        $expectedOccurrences = array($field);

        $adapter = $this->getMock('\Neverdane\Crudity\Form\Parser\NullAdapter');
        $adapter->expects($this->once())
            ->method('setHtml')
            ->with($this->equalTo($html));
        $adapter->expects($this->once())
            ->method('getFieldsOccurrences')
            ->with($this->equalTo(View::$managedTagNames))
            ->will($this->returnValue($expectedOccurrences));
        $adapter->expects($this->any())
            ->method('isFieldRelevant')
            ->with($this->equalTo('<input type="submit">'))
            ->will($this->returnValue(false));

        $parser = new Parser($html);
        $parser->setAdapter($adapter);

        $this->assertEquals(array(), $parser->getFields());
    }
}