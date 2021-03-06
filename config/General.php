<?php

declare(strict_types=1);

namespace Config;

use Config\Abstractions\SimpleModel;

use function dirname;
use function getenv;

/**
 * @method bool tweetNewEpisodes()
 * @method bool devMode()
 * @method string rootPath()
 * @method string publicPath()
 * @method string pathToStorageDirectory()
 * @method string pathToEpisodesDirectory()
 * @method string siteUrl()
 * @method string siteName()
 * @method string[] siteNameWords()
 * @method string siteTagLine()
 * @method string siteShortDescription()
 * @method string siteEmailAddress()
 * @method string twitterHandle()
 * @method array|string[][] mainNav()
 * @method array stylesheets()
 * @method array jsFiles()
 */
class General extends SimpleModel
{
    public function __construct()
    {
        $rootPath = dirname(__DIR__);

        static::$tweetNewEpisodes = getenv('TWEET_NEW_EPISODES') === 'true';

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

    public static bool $tweetNewEpisodes = false;

    public static bool $devMode = false;

    public static string $rootPath = '';

    public static string $publicPath = '';

    public static string $pathToStorageDirectory = '';

    public static string $pathToEpisodesDirectory = '';

    public static string $siteUrl = 'https://www.nightowl.fm';

    public static string $siteName = 'Night Owl';

    /** @var string[] */
    public static array $siteNameWords = [
        'Night',
        'Owl',
    ];

    public static string $siteTagLine = 'Nocturnal Podcasts by Creative People';

    public static string $siteShortDescription = 'We discuss filmmaking, technology, coffee, and bunches of other stuff for professionals, developers, designers, and enthusiasts. Each show is hand-crafted after hours.';

    public static string $siteEmailAddress = 'info@nightowl.fm';

    public static string $twitterHandle = 'nightowlfm';

    /** @var array|string[][] */
    public static array $mainNav = [
        [
            'href' => '/shows',
            'content' => 'Shows',
        ],
        [
            'href' => '/people',
            'content' => 'People',
        ],
        [
            'href' => '/about',
            'content' => 'About',
        ],
        [
            'href' => '/subscribe',
            'content' => 'Subscribe',
        ],
    ];

    /** @var string[] */
    public static array $stylesheets = ['https://fonts.googleapis.com/css?family=Ubuntu:400,400italic,500italic,500,300italic,300'];

    /** @var string[] */
    public static array $jsFiles = [
        '/assets/legacy/lib/jQuery-2.2.0.min.js',
        '/assets/legacy/script.min.js',
        'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js',
    ];
}
