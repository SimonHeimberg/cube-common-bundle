<?php

namespace Tests\CubeTools\CubeCommonBundle\Filter;

use CubeTools\CubeCommonBundle\Filter\FilterData;
use CubeTools\CubeCommonBundle\Filter\FilterQueryCondition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FilterDataTest extends TestCase
{
    /**
     * Tests data on redirect.
     */
    public function testRedirect()
    {
        $fData = new FilterData(array(
            'redirect' => 'to/somewhere',
            'filter' => new FilterQueryCondition(array('a' => 87)),
        ));
        $this->assertNotEmpty($fData->getRedirect());
        $filter = $fData->getFilter();
        $this->assertSame(null, $filter);
    }

    /**
     * Tests data when not redirecting.
     */
    public function testNoRedirect()
    {
        $fData = new FilterData(array(
            'redirect' => null,
            'filter' => new FilterQueryCondition(array('a' => 87, 'B' => 'jkd')),
            'page' => 3,
            'options' => array(),
        ));
        $this->assertEmpty($fData->getRedirect());
        $this->assertSame(3, $fData->getPage());
        $this->assertCount(0, $fData->getOptions());

        $filter = $fData->getFilter();
        $this->assertCount(2, $filter);
        $this->assertSame(87, $filter['a']);

        $this->assertFalse($fData->hasSortField(), 'hasSortField');
        $this->assertSame('k.j', $fData->getSortField('k.j'), 'getSortField');
        $this->assertSame('asc', strtolower($fData->getSortDir('xx')), 'getSortDir');

        /* enable when BadRequestHttpException is in composer
        $this->expectException(\Exception::class);
         */
        try {
            $fData->getSortField('fsldf;SELECT a');
            $this->assertTrue(false, 'no exception thrown');
        } catch (BadRequestHttpException $e) {
            $this->assertTrue(true);
        } catch (\Error $e) {
            $this->assertContains('BadRequest', $e->getMessage()); // when class not here
        }
    }

    public function testSort()
    {
        $fData = new FilterData(array(
            'redirect' => null,
            'filter' => new FilterQueryCondition(array('h' => '8')),
            'page' => 9,
            'options' => array('defaultSortDirection' => 'desc', 'defaultSortFieldName' => 'd.s'),
        ));

        $this->assertTrue($fData->hasSortField(), 'hasSortField');
        $this->assertSame('d.s', $fData->getSortField('z.y'), 'getSortField');
        $this->assertSame('desc', strtolower($fData->getSortDir('xx')), 'getSortDir');

        $options = $fData->mergeOptions(array('x' => 6, 'defaultSortFieldName' => 'n.z'));
        $this->assertEquals(array('defaultSortDirection' => 'desc', 'defaultSortFieldName' => 'd.s', 'x' => 6), $options);
    }
}
