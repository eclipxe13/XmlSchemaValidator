# Version 1.1.3
- Fix test were fialing on php 7.0 and 7.1
    - class PHPUnit_Framework_TestCase is deprecated
    - wait for 0.5 seconds after run the php server

# Version 1.1.2
- Fix project name in README.md
- Add composer.json tag xmlschema

# Version 1.1.1
- Remove typo on .travis.yml

# Version 1.1.0
- This change does not introduce any break with previous versions but add a new interface and objects
  to perform the download
- Library
    - Add the interface `XmlSchemaValidator\Downloader\DownloaderInterface` and implementations
      `XmlSchemaValidator\Downloader\CurlDownloader`,
      `XmlSchemaValidator\Downloader\NullDownloader` and
      `XmlSchemaValidator\Downloader\PhpDownloader`.
    - Make `XmlSchemaValidator\Locator` use the `DownloaderInterface`
    - Add tests for the Locator constructor and downloader getter.
- Tests
    - Add tests for the Locator constructor and downloader getter.
    - Add tests for `XmlSchemaValidator\Downloader`
    - Start php internal server to run tests on downloaders (bootstrap.php)
    - Default tests for locator uses a faker test to avoid external downloads
- Continuous Integration
    - Add 7.1
    - Drop hhvm
- Standarization
    - Rename folder `sources` to `src`
    - Rename `.php_cs` to `.php_cs.dist` require dev `friendsofphp/php-cs-fixer`
    - Add `phpcs.xml.dist`
    - Apply code style fixes from `phpcbf` and `php-cs-fixer`
- Documentation
    - Add basic usage to the validator
    - Add `CHANGELOG.md`, `TODO.md`, `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`
    - Fix badges
    - Drop coveralls

# Version 1.0.0
- Follow recommendations from sensiolabs
- Project does not depends on zip extension
- Include SensioLabs Insight