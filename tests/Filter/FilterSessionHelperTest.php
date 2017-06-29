<?php

namespace Tests\CubeTools\CubeCommonBundle\Filter;

use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler;
use Symfony\Component\Form\AbstractType as DummyFilterType;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use CubeTools\CubeCommonBundle\Filter\FilterSessionHelper;

class FilterSessionHelperTest extends FormIntegrationTestCase // this class has $this->form
{
    /**
     * tests getFilterDataFromSession and setFilterDataToSession
     */
    public function testSessionGetSet()
    {
        $mSess = new Session(new MockArraySessionStorage());
        $pageName = 'pageName_gfs';

        $this->assertNull(FilterSessionHelper::getFilterDataFromSession($mSess, $pageName));
        $filter = array('g' => 'G', 'h' => 'H');
        $tFilter = $filter;
        FilterSessionHelper::setFilterDataToSession($mSess, $pageName, $filter);
        $this->assertSame($filter, $tFilter);
        $this->assertSame($filter, FilterSessionHelper::getFilterDataFromSession($mSess, $pageName));
    }

    public function testGetFilterDataReset()
    {
        $thisUrl = '/dummy/uri/for/reset';
        $mSes = new Session(new MockArraySessionStorage());
        $mReq = Request::create($thisUrl, 'GET', array('filter_reset' => 1));
        $mReq->setSession($mSes);

        $type = $this->getMockBuilder(DummyFilterType::class)->setMethods(null)->getMock();
        $form = $this->factory->create($type);

        $d = FilterSessionHelper::getFilterData($mReq, $form);
        $this->assertSame($thisUrl, $d['redirect']);
    }

    public function testGetFilterData()
    {
        $mSes = new Session(new MockArraySessionStorage());
        $mReq = Request::create('/dummy/uri/');
        $mReq->setSession($mSes);

        $type = $this->getMockBuilder(DummyFilterType::class)->setMethods(null)->getMock();
        $bldr = $this->factory->createBuilder($type);
        $bldr->add('someChild');
        $bldr->setRequestHandler(new HttpFoundationRequestHandler());
        $form = $bldr->getForm();

        $d = FilterSessionHelper::getFilterData($mReq, $form);

        $d1 = $d;
        unset($d1['filter']);
        $this->assertEquals(array('page' => 1, 'redirect' => null, 'options' => array()), $d1);
        $this->assertCount(0, $d['filter']);

        $this->markTestIncomplete('TODO: test with data');
    }
}
