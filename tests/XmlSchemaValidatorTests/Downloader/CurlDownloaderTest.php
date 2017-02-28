<?php
namespace XmlSchemaValidatorTests\Downloader;

use XmlSchemaValidator\Downloader\CurlDownloader;

class CurlDownloaderTest extends DownloaderTestAbstract
{
    protected function setUp()
    {
        parent::setUp();
        $this->downloader = new CurlDownloader();
    }
}
