<?php
namespace XmlSchemaValidator\Downloader;

class PhpDownloader implements DownloaderInterface
{
    public function download($url, $timeout)
    {
        if (false === $tempname = tempnam(null, null)) {
            throw new \RuntimeException('Cannot create a temporary file');
        }
        $ctx = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'ignore_errors' => false,
            ],
        ]);
        // if Error Control '@' is omitted then when the download fail an error occurs and cannot return the exception
        if (! @copy($url, $tempname, $ctx)) {
            unlink($tempname);
            throw new \RuntimeException("Download fail for url $url");
        }
        return $tempname;
    }
}
