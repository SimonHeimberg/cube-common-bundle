<?php
namespace Tests\CubeTools\CubeCommonBundle\DataHandling;

use CubeTools\CubeCommonBundle\DataHandling\XMLExtractor;

class XMLExtractorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string XML source
     */
    const REQUIREMENT_SOURCE = '<?xml version="1.0" encoding="UTF-8"?>
        <elements>
          <element>
            <attribute1>at1</attribute1>
            <attribute2>at2</attribute2>
            <attribute3>at3</attribute3>
            <attribute4>at4</attribute4>
          </element>
          <element>
            <attribute1>at11</attribute1>
            <attribute2>at22</attribute2>
            <attribute3>at33</attribute3>
            <attribute4>at44</attribute4>
          </element>  
        </elements>';
    
    protected $transformations = array('attribute1' => '',
        'attribute2' => '',
        'attribute3' => '',
        'attribute4' => ''
        );
    
    protected $expectedReadArray = array(array(
        'attribute1' => 'at1',
        'attribute2' => 'at2',
        'attribute3' => 'at3',
        'attribute4' => 'at4'
    ),
    array(
        'attribute1' => 'at11',
        'attribute2' => 'at22',
        'attribute3' => 'at33',
        'attribute4' => 'at44'
    ));
    
    protected $xpath = '//elements/element';
    
    /**
     * @var XMLExtractor
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new XMLExtractor($this->xpath, $this->transformations);
        $this->object->setSource(self::REQUIREMENT_SOURCE);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    
    public function testProcessXMLWithoutNamespaces()
    {
        $readArray = $this->object->readSource();
        $this->assertEquals($this->expectedReadArray, $readArray, 'Reading of XML source was not correct!');
    }
}
