<?php
namespace XmlSchemaValidatorTests\Downloader;

use XmlSchemaValidator\Downloader\DownloaderInterface;

class FakeDownloader implements DownloaderInterface
{
    public $resources = [];

    public function __construct(array $resources = [])
    {
        $this->resources = $resources;
    }

    public function download($url, $timeout)
    {
        if (! isset($this->resources[$url])) {
            throw new \RuntimeException("Download fail for url $url Resource does not exists");
        }
        $tempname = tempnam(null, null);
        if (! @copy($this->resources[$url], $tempname)) {
            throw new \RuntimeException("Download fail for url $url Cannot place on $tempname");
        }
        return $tempname;
    }
}
