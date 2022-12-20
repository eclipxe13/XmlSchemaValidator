# CHANGELOG

This library follows [SEMVER 2.0.0](https://semver.org/spec/v2.0.0.html) convention.

Notice: Classes with tag `@internal` are only for internal use, you should not create instances of these
classes. The library will not export any of these objects outside its own scope.

## Unreleased changes

Unreleased changes will be listed here.

## Version 3.0.3 2022-12-19

When split the content of a *schema location* value, must reindex the list of values.
The following code wasn't interpreted correctly:

```xml
<r xsi:schemaLocation="
    http://test.org/schemas/ticket
    http://localhost:8999/xsd/ticket.xsd
    "/>
```

See <https://github.com/eclipxe13/XmlSchemaValidator/issues/14>. Thanks `@brankopetric`.

### Development changes

- Add `fully_qualified_strict_types` rule to `php-cs-fixer` tool.

### Unreleased 2022-12-08

This is a maintenance update that fixes continuous integration.

- Fix Psalm analysis when evaluate that `DOMXPath::query()` return *falsy*.
  It actually cannot return `false`, the expression is never malformed or the contextNode is never invalid.
- Maintenance to GitHub workflow for continuous integration.
  - Add PHP 8.2 to phpunit job matrix
  - Update GitHub actions to version 3
  - Run jobs on PHP 8.1
  - Replace `echo ::set-output` deprecated instruction
  - Remove composer installation where is not required
  - Show Psalm version (it was not visible)
- Update development tools.
- Exclude linguist detection on `tests/_files`.
- Update code styles rules as other projects.
- Implement `dependency_paths` on Scrutinizer-CI.

### Unreleased 2022-07-18

This is a maintenance update.

- Fix code style.
- Update `php-cs-fixer` config file to recent rules.
- Update development tools.
- Update Scrutinizer CI to run on PHP 7.4.

## Version 3.0.2 2022-03-08

Change return type on `Schemas::getIterator()` to include `Traversable`. This avoids compatibility issues with PHP 8.1.

Check `DOMAttr::nodeValue` can be `null`. Remove Psalm ignore.

Fix build, PHPStan ^1.4.7 does not recognize `DOMNodeList` element types. Change type to `iterable`.

Update development tools to recent versions.

This release includes Previous unreleased changes:

- 2022-02-09: Fix broken CI.

Remove unused code on test.

- 2022-02-09: Maintenance.

Update license year. Happy 2022.
Add PHP 8.1 to test matrix.
Update `psalm` config file and type annotations.
Update `.gitattributes` with project structure.
Improve internal web server start up for testing.

- 2021-11-20: Fix broken CI.

Split Continuous Integration steps into jobs.
Fix issues reported by recent version of PHPStan.

- 2021-09-26: Fix broken CI.

Run Continuous Integration on PHP 8.0. Mutation testing was failing when running on PHP 7.4.

- 2021-07-05: GitHub Actions has been failing on testing step.

`SchemaValidatorTest` now is more verbose on validations, hopping this messages let me know what the problem is.
This problem was unable to reproduce on local or `act`.

## Version 3.0.1 2020-06-18

Source Code:

Fix bug when `schemaLocation` contains `CR` or `LF`.

Development environment:

- Change default branch name from `master` to `main`.
- Update development instructions, see *Contrib* file.
- Update to Contributor Covenant Code of Conduct version 2.
- Update composer scripts.
- Update License year, happy new year on june.
- PHP Code Sniffer: configure paths in config file.
- PHPStan: configure paths in config file.
- PHPUnit: upgrade to version 9.5 config file and remove verbose by default.
- Psalm: ignore `UnnecessaryVarAnnotation` since it is not using PHP correct types.
- Include `infection` (Mutation Testing) to build pipeline.
- Migrate from Travis-CI to GitHub Actions. Thanks Travis-CI!
- Scrutinizer just receive code coverage.

## Version 3.0.0 2020-04-08

- Lot of breaking changes has been made, see [upgrade from version `2.x` to `3.x`](UPGRADE-v2-v3.md).
- Namespace change from `\XmlSchemaValidator` to `\Eclipxe\XmlSchemaValidator`.
- Now uses named exceptions, see [exceptions documentation](Exceptions.md).
- Minimal PHP version is PHP 7.3.
- `LibXmlException` is not `@internal`. Do not use it outside this project.
- `SchemaValidator` constructor uses `DOMDocument`.
  To create it from an XML content use `SchemaValidator::createFromString`.

## Version 2.1.2 2020-04-05

- Internal change to split namespace and xsd location using `preg_split`.
- Introduce deprecation notice on version `2.x`.
- Update travis badges and link.

## Version 2.1.1 2020-01-08

- Improve testing, 100% code coverage, each test class uses cover related class.
- Improve Travis-CI, do not create code coverage.
- Improve Scrutinizer-CI, create code coverage.
- Change development dependence from `phpstan/phpstan-shim` to `phpstan/phpstan`.
- Remove development dependence `overtrue/phplint`.
- Remove SensioLabs Insight.
- Update documentation, licence, changelog, etc..

## Version 2.1.0

- Allow to create a `SchemaValidator` instance using `DOMDocument`
- Run PHPUnit 7 on PHP >= 7.1
- Run PHPStan 0.10/0.11 on PHP >= 7.1

## Version 2.0.2

- Fix bug when running on PHP >= 7.1 and warning was raised when call `DOMDocument::schemaValidateSource`
  making impossible to obtain errors from `libxml_clear_errors` and throw a new `LibXmlException`
- Add a new test `SchemaValidatorTest::testValidateWithEmptySchema` to make sure that
  a `LibXmlException` exception is raised

## Version 2.0.1

- Fix bug when using windows path (backslashes), it does not validate
- Add docblock to buildSchemas
- Improve building, add PHPStan
- Use PHPLint instead of php-parallel-lint
- Update dependencies using composer-require-checker

## Version 2.0.0

- This version does not include `Locator` nor `DownloaderInterface` implementations.
  That functionality is actually outside the scope of this library and that is the reason
  why it was removed. A new library was created to implement this, take a look in
  `eclipxe/xmlresourceretriever` https://github.com/eclipxe13/XmlResourceRetriever/
- Constructor of `SchemaValidator` and `Schemas` changed.
- Add new method `SchemaValidator::validateWithSchemas` that do the same
  thing as `SchemaValidator::validate` but you must provide the `Schemas` collection
- Change from `protected` to `public` the method `SchemaValidator::buildSchemas`,
  it's useful when used with `SchemaValidator::validateWithSchemas` to change
  XSD remote locations to local or other places.
- Add `XmlSchemaValidator::LibXmlException`. It contains a method to exec a callable
  isolating the use internal errors setting and other to collect libxml errors
  and throw it like an exception.
- Rename `Schemas::getXsd` to `Schemas::getImporterXsd`
- Remove compatibility with PHP 5.6, minimum version is now PHP 7.0
- Add scalar type declarations
- Remove test assets from Mexican SAT
- Tests: Move files served by php built-in web server to from assets to public

# Version 1.1.4

- Fix implementation of libxml use internal errors on `SchemaValidator::validate`
- When creating the dom document avoid warnings (fix using the correct constant)
- Avoid using versions `@stable` in `composer.json`
- Install scrutinizer/ocular only on travis and PHP 7.1

## Version 1.1.3

- Fix test were failing on php 7.0 and 7.1
    - class PHPUnit_Framework_TestCase is deprecated
    - wait for 0.5 seconds after run the php server

## Version 1.1.2

- Fix project name in README.md
- Add composer.json tag xmlschema

## Version 1.1.1

- Remove typo on .travis.yml

## Version 1.1.0

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
    - Start php internal server to run tests on downloader (bootstrap.php)
    - Default tests for locator uses a faker test to avoid external downloads
- Continuous Integration
    - Add 7.1
    - Drop hhvm
- Standardization
    - Rename folder `sources` to `src`
    - Rename `.php_cs` to `.php_cs.dist` require dev `friendsofphp/php-cs-fixer`
    - Add `phpcs.xml.dist`
    - Apply code style fixes from `phpcbf` and `php-cs-fixer`
- Documentation
    - Add basic usage to the validator
    - Add `CHANGELOG.md`, `TODO.md`, `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`
    - Fix badges
    - Drop coveralls

## Version 1.0.0

- Follow recommendations from SensioLabs
- Project does not depends on zip extension
- Include SensioLabs Insight
