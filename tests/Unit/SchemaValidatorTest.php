<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Tests\Unit;

use DOMDocument;
use Eclipxe\XmlSchemaValidator\Exceptions\ValidationFailException;
use Eclipxe\XmlSchemaValidator\Exceptions\XmlContentIsInvalidException;
use Eclipxe\XmlSchemaValidator\Schema;
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
        /** @noinspection PhpExpressionResultUnusedInspection */
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
        $valid = $validator->validate();
        $this->assertEquals('', $validator->getLastError(), 'Last error should be empty');
        $this->assertTrue($valid);
    }

    public function testValidateWithNotListedSchemaLocations(): void
    {
        $validator = $this->utilCreateValidator('not-listed-schemalocations.xml');
        $valid = $validator->validate();
        $this->assertEquals('', $validator->getLastError(), 'Last error should be empty');
        $this->assertTrue($valid);
    }

    public function testValidateWithNotEvenSchemaLocations(): void
    {
        $validator = $this->utilCreateValidator('not-even-schemalocations.xml');
        $valid = $validator->validate();
        $this->assertNotEquals('', $validator->getLastError(), 'Last error should contain a message');
        $this->assertFalse($valid);
    }

    public function testValidateValidXmlWithSchema(): void
    {
        $validator = $this->utilCreateValidator('books-valid.xml');

        $valid = $validator->validate();
        $this->assertEquals('', $validator->getLastError(), 'Last error should be empty');
        $this->assertTrue($valid);
    }

    public function testValidateValidXmlWithTwoSchemas(): void
    {
        $validator = $this->utilCreateValidator('ticket-valid.xml');

        $valid = $validator->validate();
        $this->assertEquals('', $validator->getLastError(), 'Last error should be empty');
        $this->assertTrue($valid);
    }

    public function testValidateInvalidXmlOnlyOneSchema(): void
    {
        $validator = $this->utilCreateValidator('books-invalid.xml');

        $valid = $validator->validate();
        $this->assertStringContainsString("The attribute 'serie' is required but missing", $validator->getLastError());
        $this->assertFalse($valid);
    }

    public function testValidateInvalidXmlFirstSchemas(): void
    {
        $validator = $this->utilCreateValidator('ticket-invalid-ticket.xml');

        $valid = $validator->validate();
        $this->assertStringContainsString("The attribute 'notes' is required but missing", $validator->getLastError());
        $this->assertFalse($valid);
    }

    public function testValidateInvalidXmlSecondSchemas(): void
    {
        $validator = $this->utilCreateValidator('ticket-invalid-book.xml');

        $valid = $validator->validate();
        $this->assertStringContainsString("The attribute 'serie' is required but missing", $validator->getLastError());
        $this->assertFalse($valid);
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

    public function testBuildSchemas(): void
    {
        $expected = [
            'http://test.org/schemas/books' => 'http://localhost:8999/xsd/books.xsd',
        ];
        $validator = $this->utilCreateValidator('books-valid.xml');
        $schemas = $validator->buildSchemas();

        $retrieved = [];
        /** @var Schema $schema */
        foreach ($schemas as $schema) {
            $retrieved[$schema->getNamespace()] = $schema->getLocation();
        }
        $this->assertSame($expected, $retrieved);
    }

    public function testBuildSchemasWithoutXmlSchemaDefinition(): void
    {
        $content = <<< XML
            <?xml version="1.0" encoding="UTF-8"?>
            <root />
            XML;
        $validator = SchemaValidator::createFromString($content);
        $schemas = $validator->buildSchemas();
        $this->assertSame([], $schemas->all());
    }

    public function testBuildSchemasFromSchemaLocationValue(): void
    {
        $validator = $this->utilCreateValidator('books-valid.xml');
        $parts = [
            'uri:foo',
            'foo.xsd',
            '  uri:bar',
            "\nbar.xsd",
            "\turi:xee \r\n",
            "\nxee.xsd \r\n",
        ];
        $schemaLocationValue = implode(' ', $parts);
        $expectedParts = array_map('trim', $parts);
        $schemas = $validator->buildSchemasFromSchemaLocationValue($schemaLocationValue);
        $retrievedParts = [];
        /** @var Schema $schema */
        foreach ($schemas as $schema) {
            $retrievedParts[] = $schema->getNamespace();
            $retrievedParts[] = $schema->getLocation();
        }
        $this->assertSame($expectedParts, $retrievedParts);
    }

    public function testBuildSchemasFromSchemaLocationValueWithLeadingAndTrailingWhiteSpace(): void
    {
        /** @see https://github.com/eclipxe13/XmlSchemaValidator/issues/14 */
        // this line contains leading and trailing whitespace simulating the contents of a multiline attribute content
        $schemaLocationValue = <<< EOXML

                uri:foo
                foo.xsd
                uri:bar
                bar.xsd

            EOXML;
        $expected = 'uri:foo foo.xsd uri:bar bar.xsd';

        $validator = SchemaValidator::createFromString('<x/>');
        $schemas = $validator->buildSchemasFromSchemaLocationValue($schemaLocationValue);
        $retrieved = implode(' ', array_map(function (Schema $schema): string {
            return $schema->getNamespace() . ' ' . $schema->getLocation();
        }, iterator_to_array($schemas)));

        $this->assertSame($expected, $retrieved);
    }
}
