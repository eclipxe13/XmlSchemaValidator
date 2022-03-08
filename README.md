# eclipxe/XmlSchemaValidator

[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Scrutinizer][badge-quality]][quality]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]

This is a library to validate XML files against multiple XSD Schemas according to its own definitions.

The way this works is:

1. Receive a valid xml string as the content to be evaluated
2. Scan the file for every schemaLocation
3. Compose a schema that include all the schemas
4. Validate the XML against the composed file

## Installation

Use [composer](https://getcomposer.org/), so please run
```shell
composer require eclipxe/xmlschemavalidator
```

## Basic usage

```php
<?php
declare(strict_types=1);

use Eclipxe\XmlSchemaValidator\SchemaValidator;

$contents = file_get_contents('example.xml');
$validator = SchemaValidator::createFromString($contents);
if (! $validator->validate()) {
    echo 'Found error: ' . $validator->getLastError();
}
```

## Advanced usage

```php
<?php
declare(strict_types=1);

use Eclipxe\XmlSchemaValidator\SchemaValidator;
use Eclipxe\XmlSchemaValidator\Exceptions\ValidationFailException;

// create SchemaValidator using a DOMDocument
$document = new DOMDocument();
$document->load('example.xml');
$validator = new SchemaValidator($document);

// change schemas collection to override the schema location of a specific namespace
$schemas = $validator->buildSchemas();
$schemas->create('http://example.org/schemas/x1', './local-schemas/x1.xsd');

// validateWithSchemas does not return boolean, it throws an exception
try {
    $validator->validateWithSchemas($schemas);
} catch (ValidationFailException $ex) {
    echo 'Found error: ' . $ex->getMessage();
}
```

## Exceptions

This library creates its own specific exceptions and all of them implements `XmlSchemaValidatorException`.
Check the [exceptions' documentation](docs/Exceptions.md) for more information.

When this library uses LibXML functions, it captures the errors and throw its own exception.

## Version 1.x is deprecated

Version 1.x is no longer on development. It has a problem of concerns, the same library try to solve two different
issues: Validate an XML file and store locally a copy of the XSD files.
Version 2.x breaks this problem and give this library only one propose:
Validate an XML file against its multiple XSD files, it does not matter where are located.

## Version 2.x is deprecated

[Migration changes between version 2 and version 3](docs/UPGRADE-v2-v3.md)

Version 2.x was compatible with PHP 7 and was deprecated on 2020-04-05.

A branch `2.x` has been created, it might be installable using `composer require eclipxe/xmlschemavalidator:2.x-dev`,
but it will not be active maintained. You should change your dependency as soon as possible.

## Contributing

Contributions are welcome! Please read [CONTRIBUTING][] for details
and don't forget to take a look in the [TODO][] and [CHANGELOG][] files.

## Copyright and License

The `eclipxe/XmlSchemaValidator` library is copyright © [Carlos C Soto](https://eclipxe.com.mx/)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/eclipxe13/XmlSchemaValidator/blob/main/CONTRIBUTING.md
[changelog]: https://github.com/eclipxe13/XmlSchemaValidator/blob/main/docs/CHANGELOG.md
[todo]: https://github.com/eclipxe13/XmlSchemaValidator/blob/main/docs/TODO.md

[source]: https://github.com/eclipxe13/XmlSchemaValidator
[release]: https://github.com/eclipxe13/XmlSchemaValidator/releases
[license]: https://github.com/eclipxe13/XmlSchemaValidator/blob/main/LICENSE
[build]: https://github.com/eclipxe13/XmlSchemaValidator/actions/workflows/build.yml?query=branch:main
[quality]: https://scrutinizer-ci.com/g/eclipxe13/XmlSchemaValidator/
[coverage]: https://scrutinizer-ci.com/g/eclipxe13/XmlSchemaValidator/code-structure/main
[downloads]: https://packagist.org/packages/eclipxe/xmlschemavalidator

[badge-source]: https://img.shields.io/badge/source-eclipxe13/XmlSchemaValidator-blue.svg?style=flat-square
[badge-release]: https://img.shields.io/github/release/eclipxe13/XmlSchemaValidator.svg?style=flat-square
[badge-license]: https://img.shields.io/github/license/eclipxe13/XmlSchemaValidator.svg?style=flat-square
[badge-build]: https://img.shields.io/github/workflow/status/eclipxe13/XmlSchemaValidator/build/main?style=flat-square
[badge-quality]: https://img.shields.io/scrutinizer/g/eclipxe13/XmlSchemaValidator/main.svg?style=flat-square
[badge-coverage]: https://img.shields.io/scrutinizer/coverage/g/eclipxe13/XmlSchemaValidator/main.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/eclipxe/xmlschemavalidator.svg?style=flat-square
