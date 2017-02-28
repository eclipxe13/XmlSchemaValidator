<?php
namespace XmlSchemaValidator;

use XmlSchemaValidator\Downloader\DownloaderInterface;
use XmlSchemaValidator\Downloader\PhpDownloader;

/**
 * File locator and cache.
 * It provides a file locator and cache of urls.
 * Use the Downloader interface to provide different methods to download
 *
 * @package XmlSchemaValidator
 */
class Locator
{
    /**
     * Location of the local repository of cached files
     * @var string
     */
    protected $repository;

    /**
     * Seconds to timeout when a file is required
     * @var int
     */
    protected $timeout;

    /**
     * Seconds to know if a cached file is expired
     * @var int
     */
    protected $expire;

    /**
     * Registered urls and file location
     * @var array
     */
    protected $register = [];

    /**
     * @var FileMimeChecker
     */
    protected $mimeChecker;

    /** @var DownloaderInterface */
    protected $downloader;

    /**
     * @param string $repository Location for place to store the cached files
     * @param int $timeout Seconds to timeout when get a file when download is needed
     * @param int $expire Seconds to wait to expire cache, a value of 0 means never expires
     * @param DownloaderInterface $downloader Downloader object, if null a Downloader\PhpDownloader will be used.
     */
    public function __construct($repository = '', $timeout = 20, $expire = 0, DownloaderInterface $downloader = null)
    {
        if ('' === $repository) {
            $repository = sys_get_temp_dir();
        }
        $this->repository = (string) $repository;
        $this->timeout = max(1, (integer) $timeout);
        $this->expire = max(0, (integer) $expire);
        $this->mimeChecker = new FileMimeChecker();
        $this->downloader = $downloader ? : new PhpDownloader();
    }

    /**
     * Location of the local repository of cached files
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Seconds to timeout when a file is required
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Seconds to know if a cached file is expired
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @return DownloaderInterface
     */
    public function getDownloader()
    {
        return $this->downloader;
    }

    /**
     * Return a filename for a given URL based on the registry.
     * If the file is not registered then it is downloaded and stored in the repository location
     * @param string $url
     * @return string
     */
    public function get($url)
    {
        $this->assertUrlIsValid($url);
        // get the file or refresh the cache
        $filename = $this->cacheFileName($url);
        // register if not previously registered
        if (! $this->registered($url) || $this->register[$url] == $filename) {
            $filename = $this->cache($url);
            $this->register($url, $filename);
        }
        return $this->register[$url];
    }

    /**
     * Build a unique name for a url including the repository
     * @param string $url
     * @return string
     */
    public function cacheFileName($url)
    {
        $this->assertUrlIsValid($url);
        return $this->repository . DIRECTORY_SEPARATOR . 'cache-' . md5(strtolower($url));
    }

    /**
     * Register a url with a file without download it. However the file must exists and be readable.
     * @param string $url
     * @param string $filename
     */
    public function register($url, $filename)
    {
        $this->assertUrlIsValid($url);
        $mimetype = $this->mimeChecker->getMimeType($filename);
        if ('' === $mimetype) {
            throw new \RuntimeException("File $filename does not exists or is not readable");
        }
        if (! $this->mimeChecker->checkMime($mimetype)) {
            throw new \RuntimeException("File $filename is not a valid mime type");
        }
        $this->register[$url] = $filename;
    }

    /**
     * Unregister a url from the cache
     * @param string $url
     */
    public function unregister($url)
    {
        unset($this->register[(string) $url]);
    }

    /**
     * Return a copy of the registry
     * @return array
     */
    public function registry()
    {
        return $this->register;
    }

    /**
     * Return if a given url exists in the registry
     * @param string $url
     * @return bool
     */
    public function registered($url)
    {
        return array_key_exists($url, $this->register);
    }

    /**
     * Return the filename of an url, of needs to download a new copy then
     * it try to download, validate the mime of the downloaded file and place the file on the repository
     * @param string $url
     * @return string
     */
    protected function cache($url)
    {
        // get the filename
        $filename = $this->cacheFileName($url);
        // if no need to download then return
        if (! $this->needToDownload($filename)) {
            return $filename;
        }
        // download the file and set into a temporary file
        $temporal = $this->downloader->download($url, $this->getTimeout());
        if (! $this->mimeIsAllowed($temporal)) {
            unlink($temporal);
            throw new \RuntimeException("Downloaded file from $url is not a valid mime");
        }
        // move temporal to final destination
        // if $filename exists, it will be overwritten.
        if (! @rename($temporal, $filename)) {
            unlink($temporal);
            throw new \RuntimeException("Cannot move the temporary file to $filename");
        }
        // return the filename
        return $filename;
    }

    /**
     * append a mime to the list of mimes allowed
     * @param string $mime
     */
    public function mimeAllow($mime)
    {
        $this->mimeChecker->add($mime);
    }

    /**
     * Remove a mime to the list of mimes allowed
     * NOTE: This method does not affect previously registered urls
     * @param string $mime
     */
    public function mimeDisallow($mime)
    {
        $this->mimeChecker->remove($mime);
    }

    /**
     * return the list of allowed mimes
     * @return bool
     */
    public function mimeList()
    {
        return $this->mimeChecker->all();
    }

    /**
     * check if a the mime of a file is allowed
     *
     * @param string $filename path to the file
     * @return bool
     */
    public function mimeIsAllowed($filename)
    {
        return $this->mimeChecker->check($filename);
    }

    /**
     * Internal function to assert if URL is valid, if not throw an exception
     * @param string $url
     * @throws \RuntimeException
     */
    private function assertUrlIsValid($url)
    {
        if (empty($url)) {
            throw new \RuntimeException('Url (empty) is not valid');
        }
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException("Url $url is not valid");
        }
    }

    /**
     * Rules to determine if the file needs to be downloaded:
     * 1. file does not exists
     * 2. files do not expire
     * 3. file is expired
     * @param string $filename
     * @return bool
     */
    protected function needToDownload($filename)
    {
        // the file does not exists -> yes
        if (! file_exists($filename)) {
            return true;
        }
        // the files stored never expire -> no need
        if ($this->expire <= 0) {
            return false;
        }
        // if aging of the file is more than the expiration then need to refresh
        clearstatcache(false, $filename);
        if (time() - filemtime($filename) > $this->expire) {
            return true;
        }
        // no need to expire
        return false;
    }
}
