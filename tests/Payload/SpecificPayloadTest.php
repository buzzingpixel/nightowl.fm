<?php

declare(strict_types=1);

namespace Tests\Payload;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Throwable;

class SpecificPayloadTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testDoubleInit(): void
    {
        $objectToTest = new SpecificPayloadImplementationPayload();

        self::expectException(LogicException::class);

        self::expectExceptionMessage(
            SpecificPayloadImplementationPayload::class . ' instances can only be initialized once.'
        );

        $objectToTest->__construct();
    }

    /**
     * @throws Throwable
     */
    public function testSetInvalidProperty(): void
    {
        self::expectException(InvalidArgumentException::class);

        self::expectExceptionMessage('Property does not exist: FooBar');

        new SpecificPayloadImplementationPayload(['FooBar' => 'var']);
    }

    /**
     * @throws Throwable
     */
    public function testSetProperty(): void
    {
        $objectToTest = new SpecificPayloadImplementationPayload(['Bar' => 'TestVal']);

        self::assertSame('TestVal', $objectToTest->getBar());
    }

    public function testNoProperties(): void
    {
        $objectToTest = new SpecificPayloadImplementationPayload();

        self::assertNull($objectToTest->getBar());
    }

    public function testGetShortname(): void
    {
        $objectToTest = new SpecificPayloadImplementationPayload();

        self::assertSame(
            'SpecificPayloadImplementationPayload',
            $objectToTest->getShortName()
        );
    }

    public function testGetName(): void
    {
        $objectToTest = new SpecificPayloadImplementationPayload();

        self::assertSame(
            'SpecificPayloadImplementation',
            $objectToTest->getName()
        );
    }
}
