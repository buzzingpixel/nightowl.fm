<?php

declare(strict_types=1);

namespace App\Context\Shows\Services;

use Adbar\Dot;
use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel;
use App\Context\Feeds\AddEpisodeToChannel;
use App\Context\Feeds\GetAuthorString;
use App\Context\Keywords\Models\KeywordModel;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\Shows\Models\ShowModel;
use App\Context\Shows\ShowApi;
use App\Utilities\XmlCreateFeed;
use App\Utilities\XmlRenderFeed;
use Config\General;
use DateTimeInterface;
use DateTimeZone;
use Psr\Cache\CacheItemPoolInterface;
use Safe\DateTimeImmutable;
use SimpleXMLElement;

use function array_map;
use function assert;
use function count;
use function explode;
use function implode;

class GenerateShowRssFeed
{
    private General $generalConfig;
    private EpisodeApi $episodeApi;
    private CacheItemPoolInterface $cachePool;
    private ShowApi $showApi;
    private XmlCreateFeed $xmlCreateFeed;
    private XmlRenderFeed $xmlRenderFeed;
    private AddEpisodeToChannel $addEpisodeToChannel;
    private GetAuthorString $getAuthorString;

    public function __construct(
        General $generalConfig,
        EpisodeApi $episodeApi,
        CacheItemPoolInterface $cachePool,
        ShowApi $showApi,
        XmlCreateFeed $xmlCreateFeed,
        XmlRenderFeed $xmlRenderFeed,
        AddEpisodeToChannel $addEpisodeToChannel,
        GetAuthorString $getAuthorString
    ) {
        $this->generalConfig       = $generalConfig;
        $this->episodeApi          = $episodeApi;
        $this->cachePool           = $cachePool;
        $this->showApi             = $showApi;
        $this->xmlCreateFeed       = $xmlCreateFeed;
        $this->xmlRenderFeed       = $xmlRenderFeed;
        $this->addEpisodeToChannel = $addEpisodeToChannel;
        $this->getAuthorString     = $getAuthorString;
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
            $this->addEpisodeToChannel->add(
                $channel,
                $episode
            );
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

        $fetchModel->limit = 100;

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

        $channel->addChild('language', 'en-US');

        $channel->addChild(
            'itunes:author',
            $this->getAuthorString->get($show->hosts),
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
}
