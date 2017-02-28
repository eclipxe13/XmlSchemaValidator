<?php
namespace XmlSchemaValidator\Downloader;

interface DownloaderInterface
{
    /**
     * Download a resource from an url into a temporary file
     *
     * @param string $url
     * @param int $timeout
     * @return string temporary filename where the file was downloaded
     * @throws \RuntimeException if the download didn't success
     */
    public function download($url, $timeout);
}
