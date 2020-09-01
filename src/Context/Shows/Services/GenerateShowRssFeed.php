<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use Adbar\Dot;
use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel;
use App\Context\Keywords\Models\KeywordModel;
use App\Context\People\Models\PersonModel;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\Series\Models\SeriesModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use App\Utilities\XmlCreateFeed;
use App\Utilities\XmlRenderFeed;
use cebe\markdown\GithubMarkdown;
use Config\General;
use DateTimeInterface;
use DateTimeZone;
use Psr\Cache\CacheItemPoolInterface;
use Safe\DateTimeImmutable;
use SimpleXMLElement;

use function array_map;
use function array_values;
use function assert;
use function count;
use function explode;
use function gmdate;
use function htmlspecialchars;
use function implode;

class GenerateShowRssFeed
{
    private General $generalConfig;
    private EpisodeApi $episodeApi;
    private CacheItemPoolInterface $cachePool;
    private GithubMarkdown $markdown;
    private ShowApi $showApi;
    private XmlCreateFeed $xmlCreateFeed;
    private XmlRenderFeed $xmlRenderFeed;

    public function __construct(
        General $generalConfig,
        EpisodeApi $episodeApi,
        CacheItemPoolInterface $cachePool,
        GithubMarkdown $markdown,
        ShowApi $showApi,
        XmlCreateFeed $xmlCreateFeed,
        XmlRenderFeed $xmlRenderFeed
    ) {
        $this->generalConfig = $generalConfig;
        $this->episodeApi    = $episodeApi;
        $this->cachePool     = $cachePool;
        $this->markdown      = $markdown;
        $this->showApi       = $showApi;
        $this->xmlCreateFeed = $xmlCreateFeed;
        $this->xmlRenderFeed = $xmlRenderFeed;
    }

    public function generate(ShowModel $show): string
    {
        $episodes = $this->getEpisodes($show);

        $lastPubDate = $this->getLastPubDate($episodes, $show);

        $feed = $this->xmlCreateFeed->create();

        $channel = $this->addChannel(
            $feed,
            $show,
            $lastPubDate
        );

        foreach ($episodes as $episode) {
            $this->addEpisodeToChannel($channel, $episode);
        }

        return $this->xmlRenderFeed->render($feed);
    }

    /**
     * @return EpisodeModel[]
     */
    private function getEpisodes(ShowModel $show): array
    {
        $fetchModel = new FetchModel();

        $fetchModel->shows = [$show];

        $fetchModel->orderByPublishedAt = true;

        $fetchModel->isPublished = true;

        return $this->episodeApi->fetchEpisodes($fetchModel);
    }

    /**
     * @param EpisodeModel[] $episodes
     *
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getLastPubDate(
        array $episodes,
        ShowModel $show
    ): DateTimeImmutable {
        if (count($episodes) < 1) {
            $cacheKey = 'feed_pub_date_' . $show->id;

            $pubDateCacheItem = $this->cachePool->getItem($cacheKey);

            if (! $pubDateCacheItem->isHit()) {
                $pubDate = new DateTimeImmutable(
                    'now',
                    new DateTimeZone('UTC')
                );

                $pubDateCacheItem->key = $cacheKey;
                $pubDateCacheItem->set($pubDate);
            }

            $pubDate = $pubDateCacheItem->get();

            assert($pubDate instanceof DateTimeImmutable);

            return $pubDate;
        }

        $pubDate = $episodes[0]->publishedAt;

        assert($pubDate instanceof DateTimeImmutable);

        return $pubDate;
    }

    private function addChannel(
        SimpleXMLElement $feed,
        ShowModel $show,
        DateTimeImmutable $lastPubDate
    ): SimpleXMLElement {
        $channel = $feed->addChild('channel');

        $atomLink = $channel->addChild(
            'atom:link',
            null,
            'http://www.w3.org/2005/Atom/'
        );
        $atomLink->addAttribute('href', $show->getPublicFeedUrl());
        $atomLink->addAttribute('type', 'application/rss+xml');
        $atomLink->addAttribute('rel', 'self');

        $channel->addChild('title', $show->title);

        $channel->addChild('link', $show->getPublicFeedUrl());

        $channel->addChild('language', 'en-US');

        /** @noinspection PhpUnhandledExceptionInspection */
        $channel->addChild(
            'pubDate',
            $lastPubDate->format(DateTimeInterface::RFC2822),
        );

        $channel->addChild(
            'description',
            $show->description,
        );

        $channel->addChild(
            'itunes:summary',
            $show->description,
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $channel->addChild(
            'language',
            'en-US',
        );

        $channel->addChild(
            'itunes:author',
            $this->getAuthorString($show->hosts),
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $channel->addChild(
            'itunes:image',
            $this->showApi->getShowArtworkUrlPublic(
                $show,
                ['size' => 1400]
            ),
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $owner = $channel->addChild(
            'itunes:owner',
            null,
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $owner->addChild(
            'itunes:name',
            $this->generalConfig->siteName(),
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $owner->addChild(
            'itunes:email',
            $this->generalConfig->siteEmailAddress(),
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $channel->addChild(
            'itunes:keywords',
            implode(', ', array_map(
                static fn (KeywordModel $k) => $k->keyword,
                $show->keywords,
            )),
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $channel->addChild(
            'itunes:explicit',
            $show->explicit ? 'yes' : 'no',
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $this->addCategories(
            $channel,
            $this->sortCategories(
                $show->podcastCategories
            ),
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $channel->addChild(
            'copyright',
            'Copyright ' .
                (new DateTimeImmutable())->format('Y') .
                ' BuzzingPixel, LLC'
        );

        return $channel;
    }

    /**
     * @param PersonModel[] $people
     */
    private function getAuthorString(array $people): string
    {
        $str = '';

        $people = array_values($people);

        $totalPeople = count($people);

        foreach ($people as $i => $person) {
            $count = $i + 1;

            if ($totalPeople === 1) {
                $str = $person->getFullName();

                break;
            }

            if ($totalPeople === 2) {
                $str .= $person->getFullName();

                if ($i === 0) {
                    $str .= ' &amp; ';
                }

                continue;
            }

            $str .= $person->getFullName();

            if ($count < $totalPeople) {
                $str .= ', ';
            }

            if ($count !== $totalPeople - 1) {
                continue;
            }

            $str .= '&amp; ';
        }

        return $str;
    }

    /**
     * @param mixed[] $categoriesSorted
     */
    private function addCategories(
        SimpleXMLElement $xml,
        array $categoriesSorted
    ): void {
        foreach ($categoriesSorted as $cat) {
            $item = $xml->addChild(
                'itunes:category',
                null,
                'http://www.itunes.com/dtds/podcast-1.0.dtd',
            );

            $item->addAttribute('text', $cat['name']);

            $this->addCategories($item, $cat['children']);
        }
    }

    /**
     * @param PodcastCategoryModel[] $podcastCategories
     *
     * @return mixed[]
     */
    private function sortCategories(array $podcastCategories): array
    {
        $dot = new Dot();

        foreach ($podcastCategories as $cat) {
            $this->addSortCategory($dot, $cat);
        }

        return $dot->all();
    }

    private function addSortCategory(Dot $dot, PodcastCategoryModel $cat): void
    {
        $key = implode(
            '.children.',
            explode(
                '/',
                $cat->getParentChainWithSelfAsPath()
            )
        );

        $dot->set($key, [
            'name' => $cat->name,
            'children' => $dot->get($key)['children'] ?? [],
        ]);

        foreach ($cat->getParentChain() as $parentCat) {
            $this->addSortCategory($dot, $parentCat);
        }
    }

    private function addEpisodeToChannel(
        SimpleXMLElement $channel,
        EpisodeModel $episode
    ): void {
        $item = $channel->addChild('item');

        if ($episode->episodeType === EpisodeConstants::EPISODE_TYPE_NUMBERED) {
            $title = $episode->number . ': ' . $episode->title;
        } else {
            $title = 'Insert: ' . $episode->title;
        }

        $item->addChild('title', $title);

        $item->addChild('link', $episode->getPublicUrl());

        $guid = $item->addChild(
            'guid',
            $episode->getGuid(),
        );

        $guid->addAttribute('isPermalink', 'false');

        $item->addChild(
            'pubDate',
            $episode->publishedAt->format(DateTimeInterface::RFC2822)
        );

        $item->addChild(
            'author',
            $this->getAuthorString($episode->hosts),
        );

        $item->addChild(
            'itunes:author',
            $this->getAuthorString($episode->hosts),
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $item->addChild('description', $episode->description);

        $item->addChild(
            'itunes:summary',
            $episode->description,
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $enclosure = $item->addChild('enclosure');

        $enclosure->addAttribute(
            'url',
            $episode->getPublicFileUrl()
        );

        $enclosure->addAttribute(
            'length',
            $episode->fileSizeBytes
        );

        $enclosure->addAttribute('type', $episode->fileMimeType);

        $seconds = (int) $episode->fileRuntimeSeconds;

        $durationFormat = $seconds < 3600 ? 'i:s' : 'H:i:s';

        $item->addChild(
            'itunes:duration',
            gmdate(
                $durationFormat,
                $seconds
            ),
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $content = '';

        if (count($episode->series) > 0) {
            $content = 'Episode Series: ' . implode(
                ', ',
                array_map(
                    static function (SeriesModel $s): string {
                        return '<a href="' . $s->getPublicUrl() . '">' .
                            $s->title . '</a>';
                    },
                    $episode->series,
                )
            ) . "\n\n";
        }

        $content .= $episode->description . "\n\n";

        $content .= $episode->showNotes;

        $item->addChild(
            'content:encoded',
            htmlspecialchars(
                $this->markdown->parse($content)
            ),
            'http://purl.org/rss/1.0/modules/content/',
        );

        if (count($episode->keywords) > 0) {
            $item->addChild(
                'itunes:keywords',
                implode(', ', array_map(
                    static fn (KeywordModel $k) => $k->keyword,
                    $episode->keywords,
                )),
                'http://www.itunes.com/dtds/podcast-1.0.dtd',
            );
        }

        $channel->addChild(
            'itunes:image',
            $this->showApi->getShowArtworkUrlPublic(
                $episode->show,
                ['size' => 1400]
            ),
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );
    }
}
