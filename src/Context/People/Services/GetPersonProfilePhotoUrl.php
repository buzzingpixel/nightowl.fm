<?php

declare(strict_types=1);

namespace App\Context\People\Services;

use App\Context\DatabaseCache\DatabaseCacheItem;
use App\Context\People\Models\PersonModel;
use Config\General;
use Gumlet\ImageResize;
use GuzzleHttp\Client as GuzzleClient;
use League\Flysystem\Filesystem;
use Psr\Cache\CacheItemPoolInterface;

use function http_build_query;
use function mb_strtolower;
use function md5;
use function pathinfo;
use function trim;

use const IMAGETYPE_JPEG;

class GetPersonProfilePhotoUrl
{
    private CacheItemPoolInterface $cachePool;
    private GuzzleClient $guzzleClient;
    private General $generalConfig;
    private Filesystem $filesystem;

    public function __construct(
        CacheItemPoolInterface $cachePool,
        GuzzleClient $guzzleClient,
        General $generalConfig,
        Filesystem $filesystem
    ) {
        $this->cachePool     = $cachePool;
        $this->guzzleClient  = $guzzleClient;
        $this->generalConfig = $generalConfig;
        $this->filesystem    = $filesystem;
    }

    /**
     * @param mixed[] $opt
     */
    public function __invoke(PersonModel $person, array $opt = []): string
    {
        return $this->get($person, $opt);
    }

    /**
     * @param mixed[] $opt
     */
    public function get(PersonModel $person, array $opt = []): string
    {
        if ($person->photoPreference === 'gravatar') {
            return $this->getGravatarPhotoUrl($person, $opt);
        }

        return $this->getLocalProfilePhotoUrl($person, $opt);
    }

    /**
     * @param mixed[] $opt
     */
    private function getGravatarPhotoUrl(
        PersonModel $person,
        array $opt = []
    ): string {
        $size = (int) ($opt['size'] ?? 500);

        // r = rating, d = default, s = size
        $queryParams = [
            'r' => 'g',
            'd' => 'default_false',
            's' => $size,
        ];

        $url = 'https://www.gravatar.com/avatar/';

        $url .= md5(mb_strtolower(trim($person->email)));

        $url .= '?' . http_build_query($queryParams);

        $cacheKey = 'has_gravatar_' . $url;

        if ($this->cachePool->hasItem($cacheKey)) {
            $cacheItem = $this->cachePool->getItem($cacheKey);

            if ($cacheItem->get() === true) {
                return $url;
            }

            return $this->getLocalProfilePhotoUrlReal(
                $person,
                $opt
            );
        }

        $guzzleResponse = $this->guzzleClient->request(
            'GET',
            $url,
            ['http_errors' => false]
        );

        $hasGravatar = $guzzleResponse->getStatusCode() === 200;

        $cacheItem = new DatabaseCacheItem();

        $cacheItem->key = $cacheKey;

        $cacheItem->set($hasGravatar);

        $cacheItem->expiresAfter(864000); // 10 days

        $this->cachePool->save($cacheItem);

        if ($cacheItem->get() === true) {
            return $url;
        }

        return $this->getLocalProfilePhotoUrlReal($person, $opt);
    }

    /**
     * @param mixed[] $opt
     */
    private function getLocalProfilePhotoUrl(
        PersonModel $person,
        array $opt = []
    ): string {
        if ($person->photoFileLocation === '') {
            return $this->getGravatarPhotoUrl($person, $opt);
        }

        return $this->getLocalProfilePhotoUrlReal($person, $opt);
    }

    /**
     * @param mixed[] $opt
     */
    private function getLocalProfilePhotoUrlReal(
        PersonModel $person,
        array $opt = []
    ): string {
        $size = (int) ($opt['size'] ?? 500);

        $pub = $this->generalConfig->publicPath();

        $photosUrl = '/files/profile-photos';

        $base = $pub . $photosUrl;

        if ($person->photoFileLocation !== '') {
            $fileLocation = $base . '/' . $person->photoFileLocation;

            $pathInfo = pathinfo($fileLocation);

            $personPathInfo = pathinfo($person->photoFileLocation);

            $sizedUrl = $this->generalConfig->siteUrl() . $photosUrl .
                '/' .
                $personPathInfo['dirname'] .
                '/' .
                $size .
                '/' .
                $personPathInfo['filename'] .
                '.jpg';
        } else {
            $fileLocation = $base . '/default/user.png';

            $pathInfo = pathinfo($fileLocation);

            $sizedUrl = $this->generalConfig->siteUrl() .
                $photosUrl . '/default/' . $size . '/user.jpg';
        }

        $sizedDir = $pathInfo['dirname'] .
            '/' .
            $size;

        $sizedPath = $sizedDir .
            '/' .
            $pathInfo['filename'] .
            '.jpg';

        if ($this->filesystem->has($sizedPath)) {
            return $sizedUrl;
        }

        // Source file does not exist
        if (! $this->filesystem->has($fileLocation)) {
            return '';
        }

        $this->filesystem->createDir($sizedDir);

        $image = new ImageResize($fileLocation);
        $image->crop($size, $size);
        /**
         * @psalm-suppress InvalidScalarArgument
         * @phpstan-ignore-next-line
         */
        $image->save($sizedPath, IMAGETYPE_JPEG);

        return $sizedUrl;
    }
}
