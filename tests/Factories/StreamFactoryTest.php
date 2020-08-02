<?php

declare(strict_types=1);

namespace Tests\Factories;

use App\Factories\StreamFactory;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Stream;
use Throwable;

class StreamFactoryTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test(): void
    {
        $stream = (new StreamFactory())->make();

        self::assertInstanceOf(Stream::class, $stream);
    }
}
