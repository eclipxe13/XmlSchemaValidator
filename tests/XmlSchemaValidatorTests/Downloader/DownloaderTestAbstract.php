<?php
namespace XmlSchemaValidatorTests\Downloader;

use XmlSchemaValidator\Downloader\DownloaderInterface;
use XmlSchemaValidatorTests\TestCase;

class DownloaderTestAbstract extends TestCase
{
    /** @var DownloaderInterface */
    protected $downloader;

    public function testDownloaderDownloadSuccess()
    {
        $url = 'http://127.0.0.1:8999/cfdv32.xsd';
        $timeout = 20;
        $expected = $this->utilAssetLocation('cfdv32.xsd');
        $temporary = $this->downloader->download($url, $timeout);
        $this->assertFileEquals($expected, $temporary);
    }

    public function testDownloaderDownloadFail()
    {
        $url = 'http://127.0.0.1:8999/non-existent.xsd';
        $timeout = 1;

        $this->expectException(\Exception::class);
        $this->downloader->download($url, $timeout);
    }

    public function testDownloaderDownloadFailNoServer()
    {
        $url = 'http://127.0.0.10:8998/non-existent.xsd';
        $timeout = 1;

        $this->expectException(\Exception::class);
        $this->downloader->download($url, $timeout);
    }
}
