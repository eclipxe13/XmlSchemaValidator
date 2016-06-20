<?php

namespace XmlSchemaValidatorTests;

use \XmlSchemaValidator\Locator;

class LocatorTest extends TestCase
{
    /** @var Locator */
    private $locator;

    private $urlCfdiXsd = 'http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd';

    public function setUp()
    {
        parent::setUp();
        $this->locator = new Locator();
    }

    public function testBuildLocatorDefaultOptions()
    {
        $this->assertSame(
            sys_get_temp_dir(),
            $this->locator->getRepository(),
            'Default repository expected to be the system temp dir'
        );
        $this->assertSame(20, $this->locator->getTimeout(), 'Default timeout expected to be 20');
        $this->assertSame(0, $this->locator->getExpire(), 'Default timeout expected to be 0');
    }

    public function testBuildLocatorOptionRepository()
    {
        $loc = new Locator(__DIR__);
        $this->assertSame(__DIR__, $loc->getRepository(), 'Repository value expected to be the same as provided');
    }

    public function testBuildLocatorOptionTimeout()
    {
        $loc = new Locator('', 5);
        $this->assertSame(5, $loc->getTimeout(), 'Timeout value expected to be the same as provided');
    }

    public function testBuildLocatorOptionExpires()
    {
        $loc = new Locator('', 20, 5);
        $this->assertSame(5, $loc->getExpire(), 'Expire value expected to be the same as provided');
    }

    public function testMimes()
    {
        $this->assertCount(0, $this->locator->mimeList());
        $this->locator->mimeAllow('text/xml');
        $this->locator->mimeAllow('TEXT/xml');
        $this->locator->mimeAllow('application/xml');
        $this->locator->mimeAllow('image/png');
        $this->assertCount(3, $this->locator->mimeList(), 'Only 3 mimes stored since it is placed in lowercase');
        $this->locator->mimeDisallow('image/png');
        $this->assertCount(2, $this->locator->mimeList(), 'Only 2 mimes stored since one was removed');
        $this->locator->mimeDisallow('some');
        $list = $this->locator->mimeList();
        $this->assertCount(2, $list, 'Only 2 mimes stored since last removed does not exists');
        $this->assertContains('text/xml', $list, 'The list of mime contains text/xml');
        $this->assertContains('application/xml', $list, 'The list of mime contains application/xml');
    }

    public function testRegisterAFileThatExists()
    {
        $fxml = $this->utilAssetLocation('sample.xml');
        $this->assertFileExists($fxml, 'The file sample.xml for testing must exists');
        $this->locator->register('http://example.com/X1', $fxml);
        $this->assertSame($fxml, $this->locator->get('http://example.com/X1'), 'Register a valid file that exists');
    }

    public function testDownloadURL()
    {
        $fcfdv32 = $this->utilAssetLocation('cfdv32.xsd');
        $this->assertFileExists($fcfdv32, 'The file cfdv32.xsd for testing must exists');

        $filename = $this->locator->cacheFileName($this->urlCfdiXsd);
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->assertFileNotExists($filename, 'The cache file for cfdv32.xsd must not exists');
        $this->assertSame(
            $filename,
            $this->locator->get($this->urlCfdiXsd),
            'Cache file and received cache file from get method are the same'
        );
        $this->assertFileExists($filename, 'The cache file for cfdv32.xsd was not downloaded');
        $this->assertFileEquals(
            $fcfdv32,
            $filename,
            'The cache file for cfdv32.xsd must have the same content as the file downloaded'
        );
    }

    public function testDownloadWithExpiration()
    {
        // locator with expire settings
        $locator = new Locator('', 20, 1800);
        // files to compare
        $fcfdv32 = $this->utilAssetLocation('cfdv32.xsd');
        $cfdicache = $locator->cacheFileName($this->urlCfdiXsd);
        if (file_exists($cfdicache)) {
            unlink($cfdicache);
        }

        // download because the file does not exists
        $this->assertFileNotExists($cfdicache, 'The file must not exists');
        $locator->get($this->urlCfdiXsd);
        $this->assertFileEquals($fcfdv32, $cfdicache, 'Check if the file was since it was deleted');

        file_put_contents($cfdicache, "--override--");
        touch($cfdicache, time() - 10); // set the mtime to 10 seconds ago
        $locator->get($this->urlCfdiXsd);
        $this->assertEquals(
            "--override--",
            file_get_contents($cfdicache),
            'The file did not expires, the content is not the same that we alter'
        );

        // store other content and different the mtime
        touch($cfdicache, time() - 3600); // set the mtime to yesterday
        $locator->get($this->urlCfdiXsd);
        $this->assertFileEquals($fcfdv32, $cfdicache, 'The cache file did not expire, didnt download a fresh copy');

        // remove file
        unlink($cfdicache);
    }

    /**
     * @depends testDownloadURL
     */
    public function testDownloadAndMoveException()
    {
        $protected = '/sbin';
        if (!is_dir($protected) or is_writable($protected)) {
            $this->markTestIncomplete(
                "This test expect to find a folder $protected without write permissions,"
                ." ¿are you running this on windows or as root?"
            );
        }
        $locator = new Locator($protected);
        $filename = $locator->cacheFileName($this->urlCfdiXsd);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Cannot move the temporary file to $filename");
        $this->assertSame($filename, $locator->get($this->urlCfdiXsd), 'Return the same name');
    }

    public function testRegisterMaintenance()
    {
        $expected = [
            'http://example.com/X1' => $this->utilAssetLocation('sample.xml'),
            'http://example.com/X2' => $this->utilAssetLocation('sample.xml'),
        ];
        foreach ($expected as $key => $value) {
            $this->locator->register($key, $value);
        }
        $this->assertSame($expected, $this->locator->registry());
        $this->assertTrue($this->locator->registered('http://example.com/X1'), 'Registered URL must exists');
        $this->assertFalse($this->locator->registered('http://example.com/X3'), 'Not registered URL must not exists');
        $this->locator->unregister('http://example.com/X2');
        $this->assertFalse($this->locator->registered('http://example.com/X2'), 'Unregistered URL must not exists');
        $this->assertCount(1, $this->locator->registry(), 'The final count of the registry must be 1');
    }

    public function testRegisterAnEmptyUrl()
    {
        $url = '';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Url (empty) is not valid");
        $this->locator->register($url, 'sample.xml');
    }

    public function testRegisterAnInvalidUrl()
    {
        $url = 'not-an-url';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Url $url is not valid");
        $this->locator->register($url, 'sample.xml');
    }

    public function testRegisterNonExistentFile()
    {
        $this->locator->mimeAllow('image/png'); // allow only png images
        $file = $this->utilAssetLocation('does-not-exists.txt');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("File $file does not exists or is not readable");
        $this->locator->register('http://example.com/some', $file);
    }

    public function testRegisterAnInvalidMime()
    {
        $this->locator->mimeAllow('image/png'); // allow only png images
        $file = $this->utilAssetLocation('sample.xml');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("File $file is not a valid mime type");
        $this->locator->register('http://example.com/some', $file);
    }

    /**
     * @depends testDownloadURL
     */
    public function testDownloadWithAnInvalidMime()
    {
        $this->locator->mimeAllow('image/png'); // allow only png images
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Downloaded file from {$this->urlCfdiXsd} is not a valid mime");
        $this->locator->get($this->urlCfdiXsd);
    }

    public function testDownloadWithNonExistentUrl()
    {
        $url = 'http://example.com/non-exists.htm';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Download fail for url $url");
        $this->locator->get($url);
    }
}
