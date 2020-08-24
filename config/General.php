<?php

declare(strict_types=1);

namespace Config;

use Config\Abstractions\SimpleModel;

use function dirname;
use function getenv;

/**
 * @method bool devMode()
 * @method string rootPath()
 * @method string publicPath()
 * @method string pathToStorageDirectory()
 * @method string pathToEpisodesDirectory()
 * @method string siteUrl()
 * @method string siteName()
 * @method string twitterHandle()
 * @method array stylesheets()
 * @method array jsFiles()
 */
class General extends SimpleModel
{
    public function __construct()
    {
        $rootPath = dirname(__DIR__);

        static::$devMode = getenv('DEV_MODE') === 'true';

        static::$rootPath = $rootPath;

        static::$publicPath = $rootPath . '/public';

        static::$pathToStorageDirectory = $rootPath . '/storage';

        static::$pathToEpisodesDirectory = $rootPath . '/episodes';

        if (getenv('SITE_URL') !== false) {
            static::$siteUrl = getenv('SITE_URL');
        }

        if (
            ! static::$devMode ||
            getenv('USE_DYNAMIC_SITE_URL') !== 'true' ||
            ! isset($_SERVER['HTTP_HOST'])
        ) {
            return;
        }

        static::$siteUrl = 'https://' . $_SERVER['HTTP_HOST'];
    }

    public static bool $devMode = false;

    public static string $rootPath = '';

    public static string $publicPath = '';

    public static string $pathToStorageDirectory = '';

    public static string $pathToEpisodesDirectory = '';

    public static string $siteUrl = 'https://www.nightowl.fm';

    public static string $siteName = 'NightOwl';

    public static string $twitterHandle = 'nightowlfm';

    /** @var string[] */
    public static array $stylesheets = [];

    /** @var string[] */
    public static array $jsFiles = ['https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js'];
}
