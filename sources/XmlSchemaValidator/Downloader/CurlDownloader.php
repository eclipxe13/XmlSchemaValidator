<?php
namespace XmlSchemaValidator\Downloader;

class CurlDownloader implements DownloaderInterface
{
    public function download($url, $timeout)
    {
        if (false === $tempname = tempnam(null, null)) {
            throw new \RuntimeException('Cannot create a temporary file');
        }
        if (false === $fileHandler = fopen($tempname, 'w+')) {
            throw new \RuntimeException("Cannot create file $tempname");
        }
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_VERBOSE, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_FILE, $fileHandler);
            if (! curl_exec($ch)) {
                $curlError = ('' !== $curlError = curl_error($ch)) ? ' ' . $curlError : '';
                throw new \RuntimeException("Download fail for url $url" . $curlError);
            }
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $statusCode) {
                throw new \RuntimeException("Download fail for url $url Status code was not 200 - OK");
            }
            fclose($fileHandler);
            return $tempname;
        } catch (\Exception $ex) {
            fclose($fileHandler);
            unlink($tempname);
            throw $ex;
        }
    }
}
