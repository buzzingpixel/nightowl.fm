<?php

declare(strict_types=1);

namespace Tests\Utilities;

use App\Utilities\SimpleValidator;
use PHPUnit\Framework\TestCase;

class SimpleValidatorTest extends TestCase
{
    public function testEmailAddress(): void
    {
        self::assertFalse(SimpleValidator::email('foo'));
        self::assertFalse(SimpleValidator::email('foo@bar'));
        self::assertTrue(SimpleValidator::email('foo@bar.baz'));
    }

    public function testPassword(): void
    {
        self::assertFalse(SimpleValidator::password('foo'));
        self::assertTrue(SimpleValidator::password('foobarba'));
    }
}
