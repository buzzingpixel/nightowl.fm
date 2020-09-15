<?php

declare(strict_types=1);

namespace App\Http\ServiceSuites\TwigCache;

use League\Flysystem\Filesystem;
use ReflectionClass;
use ReflectionException;
use Twig\Cache\FilesystemCache;
use Twig\Environment;

use function exec;
use function rtrim;

class TwigCacheApi
{
    private Filesystem $filesystem;
    private Environment $twig;

    public function __construct(
        Filesystem $filesystem,
        Environment $twig
    ) {
        $this->filesystem = $filesystem;
        $this->twig       = $twig;
    }

    /**
     * Returns (bool) true if cache was cleared.
     * Returns (bool) false if cache is not enabled in this environment and
     *     cannot be cleared
     *
     * @throws ReflectionException
     */
    public function clearTwigCache(): bool
    {
        $cache = $this->twig->getCache(false);

        $isInstance = $cache instanceof FilesystemCache;

        if (! $isInstance) {
            return false;
        }

        $reflection = new ReflectionClass($cache);

        $directory = $reflection->getProperty('directory');

        $directory->setAccessible(true);

        $cacheDirGlob = rtrim(
            $directory->getValue($cache),
            '/'
        ) . '/*';

        exec('rm -rf ' . $cacheDirGlob);

        return true;
    }
}
