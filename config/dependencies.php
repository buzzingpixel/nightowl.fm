<?php

declare(strict_types=1);

use App\Cli\Services\CliQuestionService;
use App\Context\DatabaseCache\CacheItemPool;
use App\Context\Email\Adapters\MandrillSendMailAdapter;
use App\Context\Email\Configuration\MandrillConfig;
use App\Context\Email\Interfaces\SendMailAdapter;
use App\Context\Users\Models\LoggedInUser;
use App\Context\Users\UserApi;
use App\Http\AppMiddleware\StaticCacheMiddleware;
use buzzingpixel\cookieapi\CookieApi;
use buzzingpixel\cookieapi\interfaces\CookieApiInterface;
use buzzingpixel\cookieapi\PhpFunctions;
use Config\Factories\TwigEnvironmentFactory;
use Config\Logging\Logger;
use Crell\Tukio\Dispatcher;
use Crell\Tukio\OrderedListenerProvider;
use League\Flysystem\Adapter\Local as FlysystemLocalAdapter;
use League\Flysystem\Filesystem;
use Monolog\Handler\RollbarHandler;
use Monolog\Handler\StreamHandler as MonologStreamHandler;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Rollbar\Rollbar;
use Slim\Csrf\Guard as CsrfGuard;
use Slim\Exception\HttpBadRequestException;
use Slim\Psr7\Factory\ResponseFactory;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Twig\Environment as TwigEnvironment;

use function DI\autowire;
use function DI\get;

return [
    CacheItemPoolInterface::class => autowire(CacheItemPool::class),
    CliQuestionService::class => static function (ContainerInterface $di): CliQuestionService {
        return new CliQuestionService(
            $di->get(QuestionHelper::class),
            $di->get(ArgvInput::class),
            $di->get(ConsoleOutput::class)
        );
    },
    CookieApi::class => static function (): CookieApi {
        return new CookieApi(
            $_COOKIE,
            (string) getenv('ENCRYPTION_KEY'),
            new PhpFunctions()
        );
    },
    CookieApiInterface::class => get(CookieApi::class),
    CsrfGuard::class => static function (ContainerInterface $di): CsrfGuard {
        $responseFactory = $di->get(ResponseFactoryInterface::class);
        $guard           = new CsrfGuard($responseFactory);
        $guard->setFailureHandler(
            static function (ServerRequestInterface $request): void {
                throw new HttpBadRequestException(
                    $request,
                    'Invalid CSRF Token'
                );
            }
        );

        return $guard;
    },
    EventDispatcherInterface::class => autowire(Dispatcher::class)->constructorParameter(
        'logger',
        get(LoggerInterface::class),
    ),
    Filesystem::class => autowire(Filesystem::class)
        ->constructorParameter(
            'adapter',
            get(FlysystemLocalAdapter::class),
        ),
    FlysystemLocalAdapter::class => autowire(FlysystemLocalAdapter::class)
        ->constructorParameter(
            'root',
            '/',
        ),
    ListenerProviderInterface::class => get(OrderedListenerProvider::class),
    LoggedInUser::class => static function (ContainerInterface $di): LoggedInUser {
        return new LoggedInUser(
            $di->get(UserApi::class)->fetchLoggedInUser()
        );
    },
    LoggerInterface::class => static function (): LoggerInterface {
        /** @phpstan-ignore-next-line */
        $logLevel = getenv('LOG_LEVEL') ?: 'DEBUG';

        $logger = new Logger('app');

        $logPath = getenv('LOG_FILE');

        if ($logPath !== false) {
            $logger->pushHandler(
                new MonologStreamHandler(
                    $logPath,
                    constant(Logger::class . '::' . $logLevel),
                ),
            );
        }

        $rollBarAccessToken = getenv('ROLLBAR_ACCESS_TOKEN');

        if ($rollBarAccessToken !== false) {
            Rollbar::init(
                [
                    'access_token' => $rollBarAccessToken,
                    /** @phpstan-ignore-next-line */
                    'environment' => getenv('ROLLBAR_ENVIRONMENT') ?:
                        'dev',
                ]
            );

            $logger->pushHandler(
                new RollbarHandler(Rollbar::logger())
            );
        }

        return $logger;
    },
    Mandrill::class => static function (): Mandrill {
        return new Mandrill(getenv('MANDRILL_API_KEY'));
    },
    MandrillConfig::class => static function (): MandrillConfig {
        $conf = new MandrillConfig();

        $conf->fromEmail = (string) getenv('WEBMASTER_EMAIL_ADDRESS');

        $conf->fromName = (string) getenv('WEBMASTER_NAME');

        return $conf;
    },
    OrderedListenerProvider::class => static function (ContainerInterface $di): OrderedListenerProvider {
        return new OrderedListenerProvider($di);
    },
    PDO::class => static function (): PDO {
        try {
            /** @phpstan-ignore-next-line */
            $dbHost = getenv('DB_HOST') ?: 'nightowl-db';
            /** @phpstan-ignore-next-line */
            $dbPort = getenv('DB_PORT') ?: '5432';
            /** @phpstan-ignore-next-line */
            $dbName = getenv('DB_NAME') ?: 'nightowl';

            $dsn = [
                'pgsql:host=' . $dbHost,
                'port=' . $dbPort,
                'dbname=' . $dbName,
            ];

            return new PDO(
                implode(';', $dsn),
                (string) getenv('DB_USER'),
                (string) getenv('DB_PASSWORD'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (Throwable $e) {
            /** @phpstan-ignore-next-line */
            $dbHost = getenv('DB_HOST') ?: 'nightowl-db';
            /** @phpstan-ignore-next-line */
            $dbPort = getenv('DB_PORT') ?: '5432';

            $dsn = [
                'pgsql:host=' . $dbHost,
                'port=' . $dbPort,
            ];

            return new PDO(
                implode(';', $dsn),
                'postgres',
                (string) getenv('DB_PASSWORD'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }
    },
    ResponseFactoryInterface::class => autowire(ResponseFactory::class),
    SendMailAdapter::class => autowire(MandrillSendMailAdapter::class),
    StaticCacheMiddleware::class => autowire(StaticCacheMiddleware::class)
        ->constructorParameter(
            'staticCacheEnabled',
            getenv('STATIC_CACHE_ENABLED') === 'true',
        ),
    TwigEnvironment::class => static function (ContainerInterface $di): TwigEnvironment {
        return (new TwigEnvironmentFactory())($di);
    },
];
