<?php

declare(strict_types=1);

namespace App\Context\Shows\Models;

use App\Context\Keywords\Models\KeywordModel;
use App\Context\People\Models\PersonModel;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\Shows\ShowConstants;
use Config\General;
use LogicException;

use function array_walk;
use function constant;
use function explode;
use function implode;
use function in_array;
use function mb_strtolower;
use function mb_strtoupper;
use function pathinfo;
use function trim;
use function ucfirst;

/**
 * @property KeywordModel[] $keywords
 * @property PersonModel[] $hosts
 * @property PodcastCategoryModel[] $podcastCategories
 */
class ShowModel
{
    private const HAS_GET_SET = [
        'keywords',
        'hosts',
        'podcastCategories',
    ];

    public string $id = '';

    public string $title = '';

    public string $slug = '';

    public string $artworkFileLocation = '';

    public function getArtworkFileName(): string
    {
        $pathInfo = pathinfo($this->artworkFileLocation);

        /** @phpstan-ignore-next-line */
        return (string) $pathInfo['basename'];
    }

    public string $newArtworkFileLocation = '';

    public string $description = '';

    public string $status = '';

    public function getHumanReadableStatus(): string
    {
        return (string) constant(
            ShowConstants::class .
            '::' .
            'SHOW_STATUS_' .
            mb_strtoupper($this->status) .
            '_LABEL',
        );
    }

    public bool $explicit = false;

    public string $itunesLink = '';

    public string $googlePlayLink = '';

    public string $stitcherLink = '';

    public string $spotifyLink = '';

    /** @var KeywordModel[] */
    private array $keywords = [];

    public function addKeyword(KeywordModel $keywordModel): void
    {
        foreach ($this->keywords as $keyword) {
            if ($keyword->keyword !== $keywordModel->keyword) {
                continue;
            }

            return;
        }

        $this->keywords[] = $keywordModel;
    }

    public function addKeywordByString(string $keyword): void
    {
        $keywordModel = new KeywordModel();

        $keywordModel->keyword = mb_strtolower($keyword);

        $this->addKeyword($keywordModel);
    }

    /**
     * @param KeywordModel[] $keywordModels
     */
    public function setKeywords(array $keywordModels): void
    {
        $this->keywords = [];

        array_walk($keywordModels, [$this, 'addKeyword']);
    }

    public function setKeywordsFromCommaString(string $keywords): void
    {
        $this->keywords = [];

        $keywordsArray = explode(',', $keywords);

        array_walk(
            $keywordsArray,
            function (string $keyword): void {
                $keyword = trim($keyword);

                if ($keyword === '') {
                    return;
                }

                $this->addKeywordByString($keyword);
            }
        );
    }

    /**
     * @return KeywordModel[]
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /** @var PersonModel[] */
    private array $hosts = [];

    public function addHost(PersonModel $host): void
    {
        $this->hosts[] = $host;
    }

    /**
     * @param PersonModel[] $hosts
     */
    public function setHosts(array $hosts): void
    {
        $this->hosts = [];

        array_walk($hosts, [$this, 'addHost']);
    }

    /**
     * @return PersonModel[]
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    /** @var PodcastCategoryModel[] */
    private array $podcastCategories = [];

    public function addPodcastCategory(PodcastCategoryModel $category): void
    {
        $this->podcastCategories[] = $category;
    }

    /**
     * @param PodcastCategoryModel[] $categories
     */
    public function setPodcastCategories(array $categories): void
    {
        $this->podcastCategories = [];

        array_walk(
            $categories,
            [$this, 'addPodcastCategory']
        );
    }

    /**
     * @return PodcastCategoryModel[]
     */
    public function getPodcastCategories(): array
    {
        return $this->podcastCategories;
    }

    public function getPublicUrl(): string
    {
        return implode('/', [
            General::$siteUrl,
            $this->slug,
        ]);
    }

    public function getPublicFeedUrl(): string
    {
        return implode('/', [
            $this->getPublicUrl(),
            'feed',
        ]);
    }

    /**
     * @param mixed $val
     */
    public function __set(string $name, $val): void
    {
        if (
            ! in_array(
                $name,
                self::HAS_GET_SET,
                true,
            )
        ) {
            throw new LogicException('Invalid property ' . $name);
        }

        $method = 'set' . ucfirst($name);

        /** @phpstan-ignore-next-line  */
        $this->{$method}($val);
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if (
            ! in_array(
                $name,
                self::HAS_GET_SET,
                true
            )
        ) {
            throw new LogicException('Invalid property ' . $name);
        }

        $method = 'get' . ucfirst($name);

        /** @phpstan-ignore-next-line */
        return $this->{$method}();
    }

    public function __isset(string $name): bool
    {
        return in_array($name, self::HAS_GET_SET, true);
    }
}
