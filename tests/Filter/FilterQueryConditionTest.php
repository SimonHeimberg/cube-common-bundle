<?php

namespace Tests\CubeTools\CubeCommonBundle\Filter;

use CubeTools\CubeCommonBundle\Filter\FilterQueryCondition;
use PHPUnit\Framework\TestCase;

class FilterQueryConditionTest extends TestCase
{
    /**
     * Tests some basic functionality.
     */
    public function testBasics()
    {
        $filter = new FilterQueryCondition(array(
            't' => 75,
            'U' => 'kd',
            'v' => 1,
            'w' => '',
            'x' => 0,
            'y' => null,
            'z' => false,
            'a' => array(),
            'b' => new FilterQueryCondition(array()),
        ));

        $this->assertSame(75, $filter['t']);
        $this->assertCount(9, $filter);

        $this->assertNotEmpty($filter['v'], 'isset');   // 1 seems to be returned, so not assertTrue
        $this->assertEmpty($filter['d'], 'isset');
        $this->assertEmpty($filter['v']['e'], 'isset subelement');

        $this->assertTrue($filter->isActive('U'), 'isActive int');
        $this->assertFalse($filter->isActive('f'), 'isActive unset');
        $this->assertFalse($filter->isActive('w'), 'isActive ""');
        $this->assertTrue($filter->isActive('x'), 'isActive 0');
        $this->assertFalse($filter->isActive('y'), 'isActive null');
        $this->assertTrue($filter->isActive('z'), 'isActive false');
        $this->assertFalse($filter->isActive('a'), 'isActive array()');
        $this->assertFalse($filter->isActive('b'), 'isActive count=0'); // count is 0

        $this->assertTrue($filter->anyActive(), 'anyActive');

        $asP = $filter->getAsParameters();
        $this->assertCount(5, $asP);
        $this->assertArrayHasKey('x', $asP);
        $asPf = $filter->getAsParameters(array('x', 'g', 'z', 'b'));
        $this->assertCount(3, $asPf);
        $this->assertArrayNotHasKey('x', $asPf);

        $filter->andWhereEqual('dbTable', 'notSet'); // no error
        $filter->andWhereIn('dbTbl', 'y');
    }
}
