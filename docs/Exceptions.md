# Exceptions

The class throws its own exceptions, all the exceptions implements `XmlSchemaValidatorException`.

## List of exceptions

### `XmlContentIsEmptyException`

This exception extends of `InvalidArgumentException` and is thrown from `SchemaValidator::createFromString`
when you pass an empty string, to avoid this exception validate previously that the xml content is not empty.

### `XmlContentIsEmptyException`

This exception extends of `InvalidArgumentException` and is thrown from `SchemaValidator::createFromString`
when you pass a string that was not able to load as xmlbecause of malformed xml or any other libxml error.

### `ValidationFailException`

This exceptions extends of `RuntimeException` and is thrown when the validation didn't pass on method
`SchemaValidator::validateWithSchemas()`.

### `SchemaLocationPartsNotEvenException`

This exceptions extends of `RuntimeException` and is thrown when have to build an schema collection based on
the current `schemaLocation` atrributes but one of them have a odd number of elements.
This can happend using `SchemaValidator::buildSchemas` or `SchemaValidator::buildSchemasFromSchemaLocationValue`.

### `NamespaceNotFoundInSchemas`

This exception extends of `OutOfBoundsException` and is thrown when you call `Schemas::item()` with a namespace
that does not exists, verify that the namespace is registered using `Schemas::exists()`.

## Named constructors

The exceptions of this library cannot be created using new, all of them have static constructors.  
