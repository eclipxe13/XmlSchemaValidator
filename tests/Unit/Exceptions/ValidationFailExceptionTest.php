<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Tests\Unit\Exceptions;

use Eclipxe\XmlSchemaValidator\Exceptions\ValidationFailException;
use Eclipxe\XmlSchemaValidator\Tests\TestCase;
use Exception;
use RuntimeException;

class ValidationFailExceptionTest extends TestCase
{
    public function testCreate(): void
    {
        $previous = new Exception('Previous exception');
        $exception = ValidationFailException::create($previous);
        $expectedMessage = 'Schema validation failed: Previous exception';
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
