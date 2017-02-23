<?php

namespace CubeTools\CubeCommonBundle\FileFormat;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Helper for exporting html to excel file.
 */
class ExcelFile
{
    private $excelSrvc;

    /**
     * set excel service.
     *
     * @param Luiggio\ExcelBundle\Factory $excelService
     */
    public function setExcelService($excelService)
    {
        $this->excelSrvc = $excelService;
    }

    /**
     * Export html to excel file.
     *
     * @param string|Crawler|\DomNode $html     Html to export
     * @param string|null             $selector Css selector for part to export (like table or #id) , defaults to all
     *
     * @return \PHPExcel converted excel object (file)
     */
    public function exportHtml($html, $selector = null)
    {
        if (null === $this->excelSrvc) {
            throw new \Exception('phpexcel service is not installed');
        }
        $cr = $htmlStr = null;
        if (is_string($html)) {
            if (null === $selector) {
                $htmlStr = $html;
            } else {
                $cr = new Crawler();
                $cr->addHtmlContent($html);
            }
        } elseif (is_a($html, \DOMNode::class)) {
            $node = $html;
            if (null !== $selector) {
                $cr = new Crawler();
                $cr->addNode($node);
            } else {
                $htmlStr = $node->ownerDocument->saveHTML($node);
            }
        } elseif (is_a($html, Crawler::class)) {
            $cr = $html;
        } else {
            throw new \InvalidArgumentException('1st argument must by string, Crawler or DOMNode');
        }

        if (null === $htmlStr) {
            if (null !== $selector) {
                $cr = $cr->filter($selector)->first();
            }
            $node = $cr->getNode(0); // $cr->html() only returns html of children
            $htmlStr = $node->ownerDocument->saveHTML($node);
        }

        $tmpFile = $this->getTempHtmlFile($htmlStr); // as temporary file because it must have a filename

        return $this->excelSrvc->createPHPExcelObject($tmpFile['path']);
        // tmpfile is deleted automatically
    }

    public function setNextCellOnSheet(\PHPExcel_Worksheet $xlSheet, $cell)
    {
        if (TODO) {
            $xlSheet->xxx = array();
        }
    }

    public static function getNextLineAddress($range)
    {
        list ($col, $row) = $this->getColRow($startRange);
        ++$row;

        return $col.$row;
    }

    /**
     * Create response with excel download.
     *
     * @param \PHPExcel $xlObj       Excel object to create the download from
     * @param string    $filename    filename to give to the download
     * @param string    $format      format of write (like Excel2007)
     * @param string    $contentType mime content type
     *
     * @return \Symfony\Component\HtmlFoundation\Response
     */
    public function createResponse(\PHPExcel $xlObj, $filename, $format, $contentType)
    {
        if (null === $this->excelSrvc) {
            throw new \Exception('phpexcel service is not installed');
        }
        $xlWr = $this->excelSrvc->createWriter($xlObj, $format);
        $response = $this->excelSrvc->createStreamedResponse($xlWr);
        $headers = $response->headers;
        $headers->set('Content-Type', $contentType.'; charset=utf-8');
        $headers->set('Pragma', 'public');
        $headers->set('Content-Disposition', $headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        ));

        return $response;
    }

    /**
     * Generates a temporary file with the extension ".html".
     *
     * The file is deleted when the returned array is unset.
     *
     * @param string $html html to save to file
     *
     * @return array with filepath in ['path']
     */
    private function getTempHtmlFile($html)
    {
        $tf = tmpfile();
        $tfPath = stream_get_meta_data($tf)['uri'];
        rename($tfPath, $tfPath.'.html'); // rename open file would not work on windows
        $tfPath .= '.html';
        fwrite($tf, $html);

        // return reference as well, because file is deleted when reference is closed
        return array('path' => $tfPath, 'ref' => $tf);
    }
}
