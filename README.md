# eclipxe13/xmlschemavalidator

[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Scrutinizer][badge-quality]][quality]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]
[![SensioLabsInsight][badge-sensiolabs]][sensiolabs]

This is a library to validate XML files against multiple XSD Schemas according to its own definitions.

I create this project because I need to validate XML documents against not-always-known schemas
that have to be downloaded from Internet.

The way this works is:

1. Receive a valid xml file
2. Scan the file for every schemaLocation
3. Compose a file that include all the schemas
4. Validate the XML against the composed file

Features:
- It can have a repository for external schemas
- Retrieve schemas from Internet
- Storage the schemas until they expire

## Installation

Use composer, so please run `composer require eclipxe/xmlschemavalidator` or include this on your `composer.json` file:

```json
{
    "require": {
        "eclipxe/xmlschemavalidator": "@stable"
    }
}
```

## Basic usage
```php
<?php
use XmlSchemaValidator\SchemaValidator;
$validator = new SchemaValidator();
$valid = $validator->validate(file_get_contents('example.xml'));
if (! $valid) {
    echo $validator->getError(), "\n";
} else {
    echo "OK\n";
}

```

## Contributing

Contributions are welcome! Please read [CONTRIBUTING][] for details
and don't forget to take a look in the [TODO][] and [CHANGELOG][] files.

## Copyright and License

The EngineWorks\Templates library is copyright © [Carlos C Soto](https://eclipxe.com.mx/)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/eclipxe13/XmlSchemaValidator/blob/master/CONTRIBUTING.md
[changelog]: https://github.com/eclipxe13/XmlSchemaValidator/blob/master/CHANGELOG.md
[todo]: https://github.com/eclipxe13/XmlSchemaValidator/blob/master/TODO.md

[source]: https://github.com/eclipxe13/XmlSchemaValidator
[release]: https://github.com/eclipxe13/XmlSchemaValidator/releases
[license]: https://github.com/eclipxe13/XmlSchemaValidator/blob/master/LICENSE
[build]: https://travis-ci.org/eclipxe13/XmlSchemaValidator?branch=master
[quality]: https://scrutinizer-ci.com/g/eclipxe13/XmlSchemaValidator/
[sensiolabs]: https://insight.sensiolabs.com/projects/597c21ca-414b-446d-809d-7f940c3ca0a2
[coverage]: https://scrutinizer-ci.com/g/eclipxe13/XmlSchemaValidator/code-structure/master
[downloads]: https://packagist.org/packages/eclipxe/xmlschemavalidator

[badge-source]: http://img.shields.io/badge/source-eclipxe13/XmlSchemaValidator-blue.svg?style=flat-square
[badge-release]: https://img.shields.io/github/release/eclipxe13/XmlSchemaValidator.svg?style=flat-square
[badge-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[badge-build]: https://img.shields.io/travis/eclipxe13/XmlSchemaValidator/master.svg?style=flat-square
[badge-quality]: https://img.shields.io/scrutinizer/g/eclipxe13/XmlSchemaValidator/master.svg?style=flat-square
[badge-sensiolabs]: https://insight.sensiolabs.com/projects/597c21ca-414b-446d-809d-7f940c3ca0a2/mini.png
[badge-coverage]: https://img.shields.io/scrutinizer/coverage/g/eclipxe13/XmlSchemaValidator/master.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/eclipxe/xmlschemavalidator.svg?style=flat-square
