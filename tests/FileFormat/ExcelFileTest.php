<?php

namespace Tests\CubeTools\CubeCommonBundle\FileFormat;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Liuggio\ExcelBundle\Factory as ExcelFactory;
use CubeTools\CubeCommonBundle\FileFormat\Excel;

class ExcelFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideHtmlData
     */
    public function testExportAll($data)
    {
        $h2e = $this->getExportService();
        $xlo = $h2e->exportHtml($data);
        $this->assertInstanceOf('\PHPExcel', $xlo);
    }

    public function testInvalidArg()
    {
        $h2e = $this->getExportService();
        $data = new self();
        $this->setExpectedException(\InvalidArgumentException::class);
        $xlo = $h2e->exportHtml($data);
    }

    /**
     * @dataProvider provideHtmlData
     */
    public function testExportPart($data)
    {
        $selector = '#tst';
        $h2e = $this->getExportService();
        try {
            $xlo = $h2e->exportHtml($data, $selector);
        } catch (\RuntimeException $e) {
            if (false === strpos($e->getMessage(), 'Symfony CssSelector')) {
                throw $e;
            }
            // CssSelector not installed, but enough code checked
            return;
        }
        $this->assertInstanceOf('\PHPExcel', $xlo);
    }

    /**
     * @depends testExportAll
     */
    public function testCreateResponse()
    {
        $fileName = 'anyName.xlsx';
        $format = 'Excel2007';
        $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        $h2e = $this->getExportService();
        $xlo = $h2e->exportHtml('<table><tr><th>r1</th><td>d</td></tr></table>');
        $r = $h2e->createResponse($xlo, $fileName, $format, $contentType);
        $this->assertInstanceOf(Response::class, $r);
    }

    public static function provideHtmlData()
    {
        $c = new Crawler();
        $c->addHtmlContent('<p>a</p><div id="tst">3</div><span>q</span>');

        return array(
            array('string' => '<table><tr><td>1</td><td>x</td></tr><tr id="tst"><td>2</td></tr></table>'),
            array('node' => $c->getNode(0)),
            array('Crawler' => $c),
        );
    }

    private function getExportService()
    {
        $es = new Excel();
        $es->setExcelService(new ExcelFactory());

        return $es;
    }
}
