<?php

declare(strict_types=1);

namespace Tests\Templating\TwigExtensions;

use App\Templating\TwigExtensions\ReadJson;
use PHPUnit\Framework\TestCase;
use Throwable;

use function assert;
use function is_array;

class ReadJsonTest extends TestCase
{
    public function testGetFunctions(): void
    {
        $ext = new ReadJson();

        $return = $ext->getFunctions();

        self::assertCount(1, $return);

        $twigFunc = $return[0];

        self::assertSame(
            'readJson',
            $twigFunc->getName()
        );

        $callable = $twigFunc->getCallable();

        assert(is_array($callable));

        self::assertCount(2, $callable);

        self::assertSame($ext, $callable[0]);

        self::assertSame('readJsonFunction', $callable[1]);

        self::assertFalse($twigFunc->needsEnvironment());

        self::assertFalse($twigFunc->needsContext());
    }

    /**
     * @throws Throwable
     */
    public function test(): void
    {
        $ext = new ReadJson();

        $jsonDecoded = $ext->readJsonFunction(
            __DIR__ . '/TestJsonFiles/TestJsonFile.json'
        );

        self::assertSame(
            [
                'foo' => 'bar',
                'baz' => 'foo',
            ],
            $jsonDecoded,
        );
    }
}
