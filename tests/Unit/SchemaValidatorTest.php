<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Tests\Unit;

use DOMDocument;
use Eclipxe\XmlSchemaValidator\Exceptions\ValidationFailException;
use Eclipxe\XmlSchemaValidator\Exceptions\XmlContentIsInvalidException;
use Eclipxe\XmlSchemaValidator\Schemas;
use Eclipxe\XmlSchemaValidator\SchemaValidator;
use Eclipxe\XmlSchemaValidator\Tests\TestCase;
use InvalidArgumentException;

/** @covers \Eclipxe\XmlSchemaValidator\SchemaValidator */
final class SchemaValidatorTest extends TestCase
{
    public function utilCreateValidator(string $file): SchemaValidator
    {
        $location = $this->utilAssetLocation($file);
        if (! file_exists($location)) {
            $this->markTestSkipped("The file $location was not found");
        }
        $content = (string) file_get_contents($location);
        return SchemaValidator::createFromString($content);
    }

    public function testConstructUsingExistingDocument(): void
    {
        $document = new DOMDocument();
        $document->load($this->utilAssetLocation('books-valid.xml'));
        new SchemaValidator($document);
        $this->assertTrue(true, 'Expected no exception creating the schema validator using a DOMDocument');
    }

    public function testConstructorWithEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('empty');
        SchemaValidator::createFromString('');
    }

    public function testValidatePreserveGlobalEnvironment(): void
    {
        error_reporting(E_NOTICE);
        libxml_use_internal_errors(false);
        try {
            SchemaValidator::createFromString(' this is not a valid xml ');
        } catch (XmlContentIsInvalidException $exception) {
            unset($exception);
        }
        $this->assertSame(E_NOTICE, error_reporting());
        $this->assertSame(false, libxml_use_internal_errors());
    }

    public function testValidateWithNoSchema(): void
    {
        $validator = $this->utilCreateValidator('xml-without-schemas.xml');
        $this->assertTrue(
            $validator->validate(),
            'Validation without schemas and well formed document must return true'
        );
    }

    public function testValidateWithVariousWhitespaceInSchemaDeclaration(): void
    {
        $validator = $this->utilCreateValidator('books-valid-with-extra-whitespace-in-schema-declaration.xml');
        $this->assertTrue($validator->validate());
    }

    public function testValidateWithNotListedSchemaLocations(): void
    {
        $validator = $this->utilCreateValidator('not-listed-schemalocations.xml');
        $this->assertTrue($validator->validate());
    }

    public function testValidateWithNotEvenSchemaLocations(): void
    {
        $validator = $this->utilCreateValidator('not-even-schemalocations.xml');
        $this->assertFalse($validator->validate());
    }

    public function testValidateValidXmlWithSchema(): void
    {
        $validator = $this->utilCreateValidator('books-valid.xml');

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->getLastError());
    }

    public function testValidateValidXmlWithTwoSchemas(): void
    {
        $validator = $this->utilCreateValidator('ticket-valid.xml');

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->getLastError());
    }

    public function testValidateInvalidXmlOnlyOneSchema(): void
    {
        $validator = $this->utilCreateValidator('books-invalid.xml');

        $this->assertFalse($validator->validate());
        $this->assertStringContainsString("The attribute 'serie' is required but missing", $validator->getLastError());
    }

    public function testValidateInvalidXmlFirstSchemas(): void
    {
        $validator = $this->utilCreateValidator('ticket-invalid-ticket.xml');

        $this->assertFalse($validator->validate());
        $this->assertStringContainsString("The attribute 'notes' is required but missing", $validator->getLastError());
    }

    public function testValidateInvalidXmlSecondSchemas(): void
    {
        $validator = $this->utilCreateValidator('ticket-invalid-book.xml');

        $this->assertFalse($validator->validate());
        $this->assertStringContainsString("The attribute 'serie' is required but missing", $validator->getLastError());
    }

    public function testValidateWithSchemasUsingRemote(): void
    {
        $validator = $this->utilCreateValidator('books-valid.xml');
        $schemas = new Schemas();
        $schemas->create('http://test.org/schemas/books', 'http://localhost:8999/xsd/books.xsd');
        $validator->validateWithSchemas($schemas);
        $this->assertTrue(true, 'validateWithSchemas did not throw any exception');
    }

    public function testValidateWithSchemasUsingLocal(): void
    {
        $validator = $this->utilCreateValidator('books-valid.xml');
        $schemas = new Schemas();
        $schemas->create(
            'http://test.org/schemas/books',
            str_replace('/', '\\', dirname(__DIR__)) . '/public/xsd/books.xsd' // simulate windows path
        );
        $validator->validateWithSchemas($schemas);
        $this->assertTrue(true, 'validateWithSchemas did not throw any exception');
    }

    public function testValidateWithEmptySchema(): void
    {
        $validator = $this->utilCreateValidator('books-valid.xml');
        $schemas = new Schemas();
        $schemas->create(
            'http://test.org/schemas/books',
            $this->utilAssetLocation('empty.xsd')
        );

        $this->expectException(ValidationFailException::class);
        $this->expectExceptionMessage('Failed to parse the XML resource');
        $validator->validateWithSchemas($schemas);
    }
}
