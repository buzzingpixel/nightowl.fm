<?php

declare(strict_types=1);

namespace Tests\Http\Utilities\Minify;

use App\Http\Utilities\Minify\Minifier;
use PHPUnit\Framework\TestCase;
use Throwable;

use function Safe\file_get_contents;

class MinifierTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testMinifier(): void
    {
        // $testHtml = "<div \nclass='thing'\n>\nFoo Bar\n</div>";
        $testHtml = file_get_contents(__DIR__ . '/Test.html');

        $outputHtml = (new Minifier())($testHtml);

        self::assertSame(
            "<div\nclass=\"MyTestClass\"\n><h1>\nFoo Bar</h1><div>\nBaz</div></div>",
            $outputHtml
        );
    }
}
