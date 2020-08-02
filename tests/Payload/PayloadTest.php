<?php

declare(strict_types=1);

namespace Tests\Payload;

use App\Payload\Payload;
use LogicException;
use PHPUnit\Framework\TestCase;

class PayloadTest extends TestCase
{
    public function testDoubleInit(): void
    {
        $payload = new Payload(Payload::STATUS_SUCCESSFUL);

        $exception = null;

        try {
            $payload->__construct(Payload::STATUS_SUCCESSFUL);
        } catch (LogicException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(
            LogicException::class,
            $exception
        );

        self::assertSame(
            'Payload instances can only be initialized once.',
            $exception->getMessage()
        );
    }

    public function testBadStatus(): void
    {
        $exception = null;

        try {
            new Payload('FooBar');
        } catch (LogicException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(
            LogicException::class,
            $exception
        );

        self::assertSame(
            '$status is invalid',
            $exception->getMessage()
        );
    }
}
