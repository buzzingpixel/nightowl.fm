<?php

declare(strict_types=1);

namespace Config\Factories;

use App\Templating\TwigExtensions\FetchLoggedInUser;
use App\Templating\TwigExtensions\PhpFunctions;
use App\Templating\TwigExtensions\ReadJson;
use App\Templating\TwigExtensions\TemplateExists;
use App\Templating\TwigExtensions\TwigSlimFlashMessages;
use BuzzingPixel\TwigDumper\TwigDumper;
use buzzingpixel\twigsmartypants\SmartypantsTwigExtension;
use buzzingpixel\twigswitch\SwitchTwigExtension;
use buzzingpixel\twigwidont\WidontTwigExtension;
use Config\General;
use Psr\Container\ContainerInterface;
use Slim\Csrf\Guard as Csrf;
use Throwable;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

use function class_exists;
use function dirname;
use function getenv;
use function session_id;

class TwigEnvironmentFactory
{
    /**
     * @throws Throwable
     */
    public function __invoke(ContainerInterface $di): TwigEnvironment
    {
        $debug = getenv('DEV_MODE') === 'true';

        $projectPath = dirname(__DIR__, 2);

        $loader = $di->get(FilesystemLoader::class);

        $loader->addPath($projectPath . '/assets/templates');

        $twig = new TwigEnvironment(
            $loader,
            [
                'debug' => $debug,
                'cache' => $debug ? false : $projectPath . '/storage/twig',
                'strict_variables' => $debug,
            ]
        );

        if ($debug) {
            $twig->addExtension($di->get(DebugExtension::class));

            if (class_exists(TwigDumper::class)) {
                $twig->addExtension($di->get(TwigDumper::class));
            }
        }

        $twig->addExtension($di->get(PhpFunctions::class));

        $twig->addExtension($di->get(SmartypantsTwigExtension::class));

        $twig->addExtension($di->get(WidontTwigExtension::class));

        $twig->addExtension($di->get(SwitchTwigExtension::class));

        $twig->addExtension(new TemplateExists($twig->getLoader()));

        $twig->addExtension($di->get(ReadJson::class));

        if (session_id() !== '') {
            $twigMessages = $di->get(TwigSlimFlashMessages::class);

            $twig->addExtension($twigMessages);

            $postMessage = $twigMessages->getMessages('PostMessage');
        }

        $twig->addExtension($di->get(FetchLoggedInUser::class));

        $twig->addGlobal('GeneralConfig', $di->get(General::class));

        $twig->addGlobal('csrf', $di->get(Csrf::class));

        $twig->addGlobal('PostMessage', $postMessage[0] ?? []);

        return $twig;
    }
}
