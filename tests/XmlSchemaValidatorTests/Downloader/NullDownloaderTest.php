<?php
namespace XmlSchemaValidatorTests\Downloader;

use XmlSchemaValidator\Downloader\NullDownloader;

class NullDownloaderTest extends DownloaderTestAbstract
{
    protected function setUp()
    {
        parent::setUp();
        $this->downloader = new NullDownloader();
    }

    public function testDownloaderDownloadSuccess()
    {
        // NullDownloader always fail
        $this->expectException(\Exception::class);
        parent::testDownloaderDownloadSuccess();
    }
}
