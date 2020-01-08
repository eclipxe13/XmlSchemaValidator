# eclipxe/XmlSchemaValidator To Do

- [ ] Document usage examples

## Planned breaking changes

- [ ] Move sources to namespace `Eclipxe\SchemaValidator`
- [ ] Move tests to namespace `Eclipxe\SchemaValidator\Tests`
- [ ] `SchemaValidator` should be constructed using a `DOMDocument`
- [ ] `SchemaValidator` should offer a new method `createFromString`
- [ ] PHP Minimal version to 7.2 (or 7.1?)
    - Build on Scrutinizer-CI removes squizlabs/php_codesniffer, friendsofphp/php-cs-fixer, vimeo/psalm & phpstan/phpstan
    - Build on Travis-CI on 7.0 removes vimeo/psalm & phpstan/phpstan, else runs psalm & phpstan  
- [ ] Use strict types
- [ ] Review all docblocks, remove or justify

## Done

- [X] Deprecate PHP 5.6 to PHP 7.0 and phpunit from ^5.7 to ^6.3
- [X] Move from standard exceptions to library exceptions
- [X] Use better XSD samples, currently is heavely related to Mexico SAT CFDI v 3.2
- [X] Create a downloader object to separate responsabilities of Locator object
- [X] Include contribute and CoC
- [X] ~~Full coverage~~ Coverage over 90%
