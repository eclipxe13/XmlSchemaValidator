<?php
namespace XmlSchemaValidator\Downloader;

class NullDownloader implements DownloaderInterface
{
    public function download($url, $timeout)
    {
        throw new \RuntimeException("Download fail for url $url due Null downloader");
    }
}
