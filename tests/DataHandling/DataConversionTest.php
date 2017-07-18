<?php

namespace Tests\CubeTools\CubeCommonBundle\DataHandling;

use CubeTools\CubeCommonBundle\DataHandling\DataConversion;

class DataConversionTest extends \PHPUnit\Framework\TestCase
{
    public function testDataTextToDataTrue()
    {
        $data = 'true';
        DataConversion::dataTextToData($data);
        $this->assertTrue($data);
    }

    public function testDataTextToDataFalse()
    {
        $data = 'false';
        DataConversion::dataTextToData($data);
        $this->assertFalse($data);
    }

    public function testDataTextToDataNull()
    {
        $data = 'null';
        DataConversion::dataTextToData($data);
        $this->assertNull($data);
    }

    public function testDataTextToDataDataLike()
    {
        $data = '{}';
        DataConversion::dataTextToData($data);
        $this->assertSame('{}', $data);
    }

    public function testDataTextToDataInt()
    {
        $data = '12';
        DataConversion::dataTextToData($data);
        $this->assertSame(12, $data);
    }

    public function testDataTextToDataFloat()
    {
        $data = '823.674';
        DataConversion::dataTextToData($data);
        $this->assertEquals(823.674, $data, '', 0.0001);
    }

    public function testDataTextToDataAlmostNumeric()
    {
        $data = '12a';
        DataConversion::dataTextToData($data);
        $this->assertSame('12a', $data);
    }

    public function testDataTextToDataInArray()
    {
        $data = array('true', 'a1', array('false', 'n' => '81'));
        $this->assertTrue(DataConversion::dataTextToDataInArray($data), 'no failure');
        $this->assertSame($data, array(true, 'a1', array(false, 'n' => 81)));
    }
}
