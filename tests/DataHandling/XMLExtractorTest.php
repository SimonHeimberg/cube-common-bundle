<?php
namespace Tests\CubeTools\CubeCommonBundle\DataHandling;

use CubeTools\CubeCommonBundle\DataHandling\XMLExtractor;

class XMLExtractorTest extends \PHPUnit\Framework\TestCase
{
    /* data for testProcessXMLSimple */
    
    /**
     * @var string XML source
     */
    const SIMPLE_SOURCE = '<?xml version="1.0" encoding="UTF-8"?>
        <elements>
          <element>
            <element1>el1</element1>
            <element2>el2</element2>
            <element3>el3</element3>
            <element4>el4</element4>
          </element>
          <element>
            <element1>el11</element1>
            <element2>el22</element2>
            <element3>el33</element3>
            <element4>el44</element4>
          </element>  
        </elements>';
    
    protected $simpleTransformations = array('element1' => '',
        'element2' => '',
        'element3' => '',
        'element4' => '',
        );
    
    protected $simpleExpectedReadArray = array(array(
        'element1' => 'el1',
        'element2' => 'el2',
        'element3' => 'el3',
        'element4' => 'el4',
    ),
    array(
        'element1' => 'el11',
        'element2' => 'el22',
        'element3' => 'el33',
        'element4' => 'el44',
    ));
    
    /* end of data for testProcessXMLSimple */
    /* data for testProcessXMLWithAttributeInFirstElement */
    
    /**
     * @var string XML source with element
     */
    const ATTRIBUTE_FIRST_ELEMENT_SOURCE = '<?xml version="1.0" encoding="UTF-8"?>
        <elements>
          <element attribute1="at1" attribute2="at2">
            <element1>el1</element1>
            <element2>el2</element2>
            <element3>el3</element3>
            <element4>el4</element4>
          </element>
          <element attribute1="at3" attribute2="at4">
            <element1>el11</element1>
            <element2>el22</element2>
            <element3>el33</element3>
            <element4>el44</element4>
          </element>  
        </elements>';
    
    protected $attributeFirstElementTransformations = array('/@attribute1' => '',
        '/@attribute2' => '',
        );
    
    protected $attributeFirstElementExpectedReadArray = array(array(
        '/@attribute1' => 'at1',
        '/@attribute2' => 'at2',
    ),
    array(
        '/@attribute1' => 'at3',
        '/@attribute2' => 'at4',
    ));
    
    /* end of data for testProcessXMLWithAttributeInFirstElement */
    
    /* data for testProcessXMLWithAttributeInChild */
    
    /**
     * @var string XML source with element
     */
    const ATTRIBUTE_IN_CHILD = '<?xml version="1.0" encoding="UTF-8"?>
        <elements>
          <element>
            <element1 attribute1="at1">el1</element1>
            <element2 attribute1="at2">el2</element2>
            <element3>el3</element3>
            <element4>el4</element4>
          </element>
          <element>
            <element1 attribute1="at3">el11</element1>
            <element2 attribute1="at4">el22</element2>
            <element3>el33</element3>
            <element4>el44</element4>
          </element>  
        </elements>';
    
    protected $attributeInChildTransformations = array('element1/@attribute1' => '',
        'element2/@attribute1' => '',
        );
    
    protected $attributeInChildExpectedReadArray = array(array(
        'element1/@attribute1' => 'at1',
        'element2/@attribute1' => 'at2',
    ),
    array(
        'element1/@attribute1' => 'at3',
        'element2/@attribute1' => 'at4',
    ));
    
    /* end of data for testProcessXMLWithAttributeInChild */
    /* data for testProcessXMLSimpleTranslation */
    
    protected $simpleTranslationExpectedReadArray = array(array(
        'element_first' => 'el1',
        'element2' => 'el2',
        'element3' => 'el3',
        'element4' => 'el4',
    ),
    array(
        'element_first' => 'el11',
        'element2' => 'el22',
        'element3' => 'el33',
        'element4' => 'el44',
    ));
    
    /* end of data for testProcessXMLSimpleTranslation */
    /* data for testProcessXMLSimpleReplaceSourceElement */
    
    protected $replaceSourceElementExpectedReadArray = array(array(
        'dynamicElementName' => 'el1',
        'element2' => 'el2',
        'element3' => 'el3',
        'element4' => 'el4',
    ),
    array(
        'dynamicElementName' => 'el11',
        'element2' => 'el22',
        'element3' => 'el33',
        'element4' => 'el44',
    ));
    
    /* end of data for testProcessXMLSimpleReplaceSourceElement */
    
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
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    
    public function testProcessXMLSimple()
    {
        $this->object = new XMLExtractor($this->xpath, $this->simpleTransformations);
        $this->object->setSource(self::SIMPLE_SOURCE);
        $readArray = $this->object->readSource();
        $this->assertEquals($this->simpleExpectedReadArray, $readArray, 'Reading of XML source was not correct!');
    }
    
    public function testProcessXMLWithAttributeInFirstElement()
    {
        $this->object = new XMLExtractor($this->xpath, $this->attributeFirstElementTransformations);
        $this->object->setSource(self::ATTRIBUTE_FIRST_ELEMENT_SOURCE);
        $readArray = $this->object->readSource();
        $this->assertEquals($this->attributeFirstElementExpectedReadArray, $readArray, 'Reading of XML source was not correct!');
    }
    
    public function testProcessXMLWithAttributeInChild()
    {
        $this->object = new XMLExtractor($this->xpath, $this->attributeInChildTransformations);
        $this->object->setSource(self::ATTRIBUTE_IN_CHILD);
        $readArray = $this->object->readSource();
        $this->assertEquals($this->attributeInChildExpectedReadArray, $readArray, 'Reading of XML source was not correct!');
    }
    
    public function testProcessXMLSimpleTranslation()
    {
        $this->object = new XMLExtractor($this->xpath, $this->simpleTransformations);
        $this->object->addSourceTranslation('element1', 'element_first');
        $this->object->setSource(self::SIMPLE_SOURCE);
        $readArray = $this->object->readSource();
        $this->assertEquals($this->simpleTranslationExpectedReadArray, $readArray, 'Reading of XML source was not correct!');
    }
    
    public function testProcessXMLSimpleReplaceSourceElement()
    {
        $this->object = new XMLExtractor($this->xpath, $this->simpleTransformations);
        $this->object->addOrReplaceSourceElement('element1', 'dynamicElementName');
        $this->object->setSource(self::SIMPLE_SOURCE);
        $readArray = $this->object->readSource();
        $this->assertEquals($this->replaceSourceElementExpectedReadArray, $readArray, 'Reading of XML source was not correct!');
    }
}
