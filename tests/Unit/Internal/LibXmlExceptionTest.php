<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Tests\Unit\Internal;

use DOMDocument;
use Eclipxe\XmlSchemaValidator\Internal\LibXmlException;
use Eclipxe\XmlSchemaValidator\Tests\TestCase;
use LibXMLError;
use LogicException;

/** @covers \Eclipxe\XmlSchemaValidator\Internal\LibXmlException */
final class LibXmlExceptionTest extends TestCase
{
    public function testConstructorWithEmptyErrors(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Errors array of LibXmlError is empty');
        LibXmlException::create('foo', []);
    }

    public function testConstructorWithNonLibXmlError(): void
    {
        /** @var LibXMLError[] $errors */
        $errors = ['x-index' => (object) []];
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error index x-index is not a LibXmlError');
        LibXmlException::create('foo', $errors);
    }

    public function testCreateFromLibXmlWithoutAnyErrorReturnsNull(): void
    {
        $this->assertNull(LibXmlException::createFromLibXml());
    }

    public function testCreateFromLibXmlWithErrors(): void
    {
        $errorReporting = error_reporting(0);
        $libXmlUseInternalErrors = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            $document->loadXML('<root invalid xml');
            $errorsBeforeCreateLibXmlException = libxml_get_errors();
            $libXmlException = LibXmlException::createFromLibXml();
            $errorsAfterCreateLibXmlException = libxml_get_errors();
        } finally {
            error_reporting($errorReporting);
            libxml_use_internal_errors($libXmlUseInternalErrors);
        }

        $this->assertNotEmpty($errorsBeforeCreateLibXmlException, 'It must have at least one error');
        $this->assertEmpty($errorsAfterCreateLibXmlException, 'It must have empty list of errors');
        /** @var LibXmlException $libXmlException */
        $this->assertNotEmpty($libXmlException->getErrors(), 'LibXmlErrors must be captured inside LibXmlException');
    }

    public function testCallUseInternalErrorsCatchOnlyTheError(): void
    {
        // setup to use internal errors and disable error reporting
        libxml_use_internal_errors(true);
        error_reporting(0);
        // create an error
        $document = new DOMDocument();
        $document->loadXML('<r>');
        // run the code that create the LibXmlException
        /** @var LibXmlException|null $foundException */
        $foundException = null;
        try {
            LibXmlException::useInternalErrors(
                function (): void {
                    $document = new DOMDocument();
                    $document->loadXML('invalid xml');
                }
            );
        } catch (LibXmlException $exception) {
            $foundException = $exception;
        }
        if (null === $foundException) {
            $this->fail('The LibXmlException was not thrown');
        }

        /** @var LibXmlException $foundException */
        $this->assertStringStartsWith('Start tag expected', $foundException->getMessage());
        $this->assertCount(1, $foundException->getErrors(), 'It should only exists 1 error');
        $this->assertContainsOnlyInstancesOf(LibXMLError::class, $foundException->getErrors());
    }

    public function testCallUseInternalErrorsRestoreGlobalSettings(): void
    {
        // setup global environment
        $libxmlUseInternalErrors = false;
        $errorReportingLevel = E_ERROR;
        libxml_use_internal_errors($libxmlUseInternalErrors);
        error_reporting($errorReportingLevel);
        LibXmlException::useInternalErrors(function (): void {
        });
        $this->assertSame($libxmlUseInternalErrors, libxml_use_internal_errors());
        $this->assertSame($errorReportingLevel, error_reporting());
    }

    public function testCallUseInternalErrorsReturnValue(): void
    {
        /** @var string $returnedValue */
        $returnedValue = LibXmlException::useInternalErrors(
            function (): string {
                $document = new DOMDocument();
                $document->loadXML('<r/>');
                return $document->saveXML() ?: '';
            }
        );
        $this->assertXmlStringEqualsXmlString('<r/>', $returnedValue);
    }
}
