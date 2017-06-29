<?php

namespace CubeTools\CubeCommonBundle\FileFormat;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Adds error throwing and filename adaption to php zip class.
 */
class ZipArchiveChecked extends \ZipArchive
{
    public function open($filename, $flags = null)
    {
        $r = parent::open($filename, $flags);
        $this->checkStatusAndRaise($r);

        return $r;
    }

    public function close()
    {
        if (0 === $this->numFiles) {
            // make sure "something" is in the zip file, because otherwise it will not be created at all
            $this->addEmptyDir('.');
        }
        parent::close();
        $this->checkStatusAndRaise();
    }

    public function addFile($filename, $localname = null, $start = 0, $length = 0)
    {
        if ($localname) {
            /* Solution according to http://php.net/manual/de/function.iconv.php comment.
               Note the following: https://bugs.php.net/bug.php?id=65815 ; This may not be perfect. */
            $localname = \iconv('UTF-8', 'IBM850', $localname);
        }

        return parent::addFile($filename, $localname, $start, $length);
    }

    public function addFromString($localname, $contents)
    {
        $localname = \iconv('UTF-8', 'IBM850', $localname);

        return parent::addFromString($localname, $contents);
    }

    public function checkStatusAndRaise($retStatus = null)
    {
        if (true !== $retStatus && 0 !== $this->status) {
            $msg = 'ZipFile failed: ';
            if (null !== $retStatus) {
                $msg .= '(retStatus '.$retStatus.') ';
            }
            throw new \Exception($msg.$this->getStatusString());
        }
    }

    /**
     * Creates a BinaryFileResponse with this zip file.
     *
     * @return BinaryFileResponse
     */
    public function getZipResponseAndClose()
    {
        $zipFilePath = $this->filename;
        $this->close();

        return static::createZipResponse($zipFilePath, basename($zipFilePath));
    }

    /**
     * Creates a BinaryFileResponse with the zip file.
     *
     * @param string $zipFilePath
     * @param string $zipFilename
     *
     * @return BinaryFileResponse
     */
    public static function createZipResponse($zipFilePath, $zipFilename)
    {
        $response = new BinaryFileResponse($zipFilePath);

        $response->trustXSendfileTypeHeader();
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $zipFilename,
            preg_replace('/[^\x20-\x7E]/', '', $zipFilename)  // this is the ASCII fallback
        );

        return $response;
    }
}
