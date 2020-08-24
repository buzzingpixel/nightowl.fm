<?php

declare(strict_types=1);

namespace App\Context\Episodes\Models;

use App\Context\Keywords\Models\KeywordModel;
use App\Context\People\Models\PersonModel;
use App\Context\Series\Models\SeriesModel;
use App\Context\Shows\Models\ShowModel;
use DateTimeZone;
use LogicException;
use Safe\DateTimeImmutable;

use function array_walk;
use function explode;
use function in_array;
use function mb_strtolower;
use function pathinfo;
use function trim;
use function ucfirst;

/**
 * @property PersonModel[] $hosts
 * @property PersonModel[] $guests
 * @property KeywordModel[] $keywords
 * @property SeriesModel[] $series
 */
class EpisodeModel
{
    private const HAS_GET_SET = [
        'hosts',
        'guests',
        'keywords',
        'series',
    ];

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );
    }

    public string $id = '';

    public ShowModel $show;

    public string $title = '';

    public string $status = '';

    public string $description = '';

    public string $fileLocation = '';

    public function getFileName(): string
    {
        $pathInfo = pathinfo($this->fileLocation);

        /** @phpstan-ignore-next-line */
        return (string) $pathInfo['basename'];
    }

    public string $newFileLocation = '';

    public float $fileRuntimeSeconds = 0.0;

    public string $fileSizeBytes = '';

    public string $fileMimeType = '';

    public string $fileFormat = '';

    public string $episodeType = '';

    public bool $explicit = false;

    public string $showNotes = '';

    public ?DateTimeImmutable $publishAt = null;

    public bool $isPublished = false;

    public int $number = 0;

    public int $displayOrder = 0;

    public DateTimeImmutable $createdAt;

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

    /** @var PersonModel[] */
    private array $guests = [];

    public function addGuest(PersonModel $guest): void
    {
        $this->guests[] = $guest;
    }

    /**
     * @param PersonModel[] $guests
     */
    public function setGuests(array $guests): void
    {
        $this->guests = [];

        array_walk($guests, [$this, 'addGuest']);
    }

    /**
     * @return PersonModel[]
     */
    public function getGuests(): array
    {
        return $this->guests;
    }

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

    /** @var SeriesModel[] */
    private array $series = [];

    public function addSeries(SeriesModel $series): void
    {
        $this->series[] = $series;
    }

    /**
     * @param SeriesModel[] $series
     */
    public function setSeries(array $series): void
    {
        $this->series = [];

        array_walk($series, [$this, 'addSeries']);
    }

    /**
     * @return SeriesModel[]
     */
    public function getSeries(): array
    {
        return $this->series;
    }

    /**
     * @param mixed $val
     */
    public function __set(string $name, $val): void
    {
        if (! in_array($name, self::HAS_GET_SET)) {
            throw new LogicException('Invalid property ' . $name);
        }

        $method = 'set' . ucfirst($name);

        $this->{$method}($val);
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if (! in_array($name, self::HAS_GET_SET)) {
            throw new LogicException('Invalid property ' . $name);
        }

        $method = 'get' . ucfirst($name);

        return $this->{$method}();
    }

    public function __isset(string $name): bool
    {
        return in_array($name, self::HAS_GET_SET);
    }
}
