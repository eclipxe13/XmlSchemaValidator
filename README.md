# eclipxe/xmlschemavalidator

[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Scrutinizer][badge-quality]][quality]
[![SensioLabsInsight][badge-sensiolabs]][sensiolabs]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]

This is a library to validate against multiple XSD Schemas.

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

## TODO

All your help is very appreciated, please contribute with testing, ideas, code, documentation, coffees, etc.

- [ ] Full coverage
- [ ] Document usage examples
- [ ] Include sensiolabs checks
- [ ] Move from standard exceptions to library exceptions
- [ ] Include contribute and CoC
- [ ] Use better XSD samples, currently is too related to Mexico SAT CFDI v 3.2

Before contribute please run phpunit and ensure that you are following PSR2, it would be better if you run php-cs-fixer
to follow the specific project rules.

## License

License MIT - Copyright (c) 2014 - 2016 Carlos Cortés Soto

[source]: https://github.com/eclipxe13/XmlSchemaValidator
[release]: https://github.com/eclipxe13/XmlSchemaValidator/releases
[license]: https://github.com/eclipxe13/XmlSchemaValidator/blob/master/LICENSE
[build]: https://travis-ci.org/eclipxe13/XmlSchemaValidator
[quality]: https://scrutinizer-ci.com/g/eclipxe13/XmlSchemaValidator/
[sensiolabs]: https://insight.sensiolabs.com/projects/<<SENSIOLABS>>
[coverage]: https://coveralls.io/github/eclipxe13/XmlSchemaValidator?branch=master
[downloads]: https://packagist.org/packages/eclipxe/xmlschemavalidator

[badge-source]: http://img.shields.io/badge/source-eclipxe13/XmlSchemaValidator-blue.svg?style=flat-square
[badge-release]: https://img.shields.io/github/release/eclipxe13/XmlSchemaValidator.svg?style=flat-square
[badge-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[badge-build]: https://img.shields.io/travis/eclipxe13/XmlSchemaValidator.svg?style=flat-square
[badge-quality]: https://img.shields.io/scrutinizer/g/eclipxe13/XmlSchemaValidator/master.svg?style=flat-square
[badge-sensiolabs]: https://img.shields.io/sensiolabs/i/<<SENSIOLABS>>.svg?style=flat-square
[badge-coverage]: https://coveralls.io/repos/github/eclipxe13/XmlSchemaValidator/badge.svg?branch=master
[badge-downloads]: https://img.shields.io/packagist/dt/eclipxe/xmlschemavalidator.svg?style=flat-square
