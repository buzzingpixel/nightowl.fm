<?php

declare(strict_types=1);

namespace App;

use Exception;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Only if we really must (like to access the DI in a migration
 */
class Globals
{
    private static bool $hasInitialized = false;

    private static ContainerInterface $di;

    /**
     * @throws Throwable
     */
    public static function init(
        ContainerInterface $di
    ): void {
        if (self::$hasInitialized) {
            throw new Exception(
                'Singleton Globals may only call init once',
            );
        }

        self::$di = $di;

        self::$hasInitialized = true;
    }

    public static function di(): ContainerInterface
    {
        return self::$di;
    }
}
