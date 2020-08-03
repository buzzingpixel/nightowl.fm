<?php

declare(strict_types=1);

namespace Tests\Templating\TwigExtensions;

use App\Templating\TwigExtensions\TemplateExists;
use PHPUnit\Framework\TestCase;
use Tests\TestConfig;
use Twig\Environment as TwigEnvironment;

class TemplateExistsTest extends TestCase
{
    public function test(): void
    {
        $twig = TestConfig::$di->get(TwigEnvironment::class);

        $templateExists = new TemplateExists($twig->getLoader());

        $functions = $templateExists->getFunctions();

        self::assertCount(1, $functions);

        $function = $functions[0];

        self::assertSame('templateExists', $function->getName());

        /** @var mixed[] $callable */
        $callable = $function->getCallable();

        self::assertSame($templateExists, $callable[0]);

        self::assertSame('templateExists', $callable[1]);

        self::assertFalse($templateExists->templateExists('FooBar'));

        self::assertTrue($templateExists->templateExists('Http/HomePage.twig'));
    }
}
