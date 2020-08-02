<?php

declare(strict_types=1);

namespace Tests\Utilities;

use App\Utilities\CaseConversionUtility;
use PHPUnit\Framework\TestCase;
use Tests\TestConfig;

class CaseConversionUtilityTest extends TestCase
{
    private CaseConversionUtility $caseConversionUtility;

    public function testConvertStringToPascale(): void
    {
        self::assertSame(
            'FooBarBaz',
            $this->caseConversionUtility->convertStringToPascale(
                'foo bar baz'
            )
        );

        self::assertSame(
            'FooBarBaz',
            $this->caseConversionUtility->convertStringToPascale(
                'fooBarBaz'
            )
        );
    }

    public function testConvertStringToCamel(): void
    {
        self::assertSame(
            'fooBarBaz',
            $this->caseConversionUtility->convertStringToCamel(
                'FooBarBaz'
            )
        );

        self::assertSame(
            'fooBarBaz',
            $this->caseConversionUtility->convertStringToCamel(
                'foo bar baz'
            )
        );
    }

    protected function setUp(): void
    {
        $this->caseConversionUtility = TestConfig::$di->get(
            CaseConversionUtility::class
        );
    }
}
