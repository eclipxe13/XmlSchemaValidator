# Upgrade from version `2.x` to `3.x`

## Notable changes

### SchemaValidator construct

Now `SchemaValidator` must be created using a `DOMDocument` as parameter, to create an instance using
an XML string use the static method `SchemaValidator::createFromString`. 

### Namespace

The namespace changes from `\XmlSchemaValidator` to `\Eclipxe\XmlSchemaValidator`.

This is because the project was not following the `vendor\product` convention.

### Exceptions

The library now uses own exceptions, check the [exceptions documentation](Exceptions.md).

All exceptions are annotated on `phpdoc` blocks.

### PHP minimal version

Minimal version changes from `7.0` to `7.3`.

As of 2020-04-05 versions `7.0` and `7.1` were on *End of life*;
version `7.2` is on security fixes only until 2020-11-30;
version `7.3` has active support and has security fixes until 2021-12-06.

## Internal changes

The following changes are about the library, not about your implementation.

### Internal `LibXmlException`

`LibXmlException` is now `@internal`, it is not to be used from outside library scope. It can ce exposed
by a named exception as previous, but it is fine since is just a `Throwable`.

### Strict mode

Strict type declaration `declare(strict_types=1);` has been set to all files.

Functions that does not return are defined as `void`.

### File locations

- `Eclipxe\XmlSchemaValidator => src/`
- `Eclipxe\XmlSchemaValidator\Tests => tests/`

### Development tools

Development tools (except `PHPUnit`) are installed into `tools/` directory using the tool
[`phive`](https://phar.io/).

This helps to memory usage on IDE like `PhpStorm` and to have a light development dependencies on composer.
