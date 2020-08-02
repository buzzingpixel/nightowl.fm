<?php

declare(strict_types=1);

namespace Tests;

use Di\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Csrf\Guard as CsrfGuard;

use function assert;
use function dirname;

class TestConfig
{
    public const TESTS_BASE_PATH = __DIR__;

    public static ContainerInterface $di;

    /** @var mixed[] */
    public static array $flashStorage = [];

    /** @var mixed[] */
    public static array $csrfStorage = [];

    public function __construct()
    {
        if (isset(static::$di)) {
            return;
        }

        $bootstrap = include dirname(__DIR__) . '/config/bootstrap.php';

        $di = $bootstrap();

        assert($di instanceof Container);

        $di->set(
            CsrfGuard::class,
            static function (ContainerInterface $di): CsrfGuard {
                return new CsrfGuard(
                    $di->get(ResponseFactoryInterface::class),
                    'csrf',
                    self::$csrfStorage
                );
            }
        );

        static::$di = $di;
    }
}
