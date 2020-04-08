<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Tests\Unit\Exceptions;

use Eclipxe\XmlSchemaValidator\Exceptions\XmlContentIsInvalidException;
use Eclipxe\XmlSchemaValidator\Tests\TestCase;
use Exception;
use InvalidArgumentException;

class XmlContentIsInvalidExceptionTest extends TestCase
{
    public function testCreate(): void
    {
        $previous = new Exception('Previous exception');
        $exception = XmlContentIsInvalidException::create($previous);
        $expectedMessage = 'The xml contents cannot be loaded: Previous exception';
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
