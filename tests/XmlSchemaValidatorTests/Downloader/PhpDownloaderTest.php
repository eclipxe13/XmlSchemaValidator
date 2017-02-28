<?php
namespace XmlSchemaValidatorTests\Downloader;

use XmlSchemaValidator\Downloader\PhpDownloader;

class PhpDownloaderTest extends DownloaderTestAbstract
{
    protected function setUp()
    {
        parent::setUp();
        $this->downloader = new PhpDownloader();
    }
}
