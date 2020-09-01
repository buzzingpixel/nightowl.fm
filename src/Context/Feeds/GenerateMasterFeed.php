<?php

declare(strict_types=1);

namespace App\Context\Feeds;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Episodes\Models\FetchModel;
use App\Context\Shows\ShowConstants;
use App\Utilities\XmlCreateFeed;
use App\Utilities\XmlRenderFeed;
use Config\General;
use DateTimeInterface;
use DateTimeZone;
use Psr\Cache\CacheItemPoolInterface;
use Safe\DateTimeImmutable;
use SimpleXMLElement;

use function assert;
use function count;

class GenerateMasterFeed
{
    private EpisodeApi $episodeApi;
    private CacheItemPoolInterface $cachePool;
    private XmlCreateFeed $xmlCreateFeed;
    private XmlRenderFeed $xmlRenderFeed;
    private General $generalConfig;
    private AddEpisodeToChannel $addEpisodeToChannel;

    public function __construct(
        EpisodeApi $episodeApi,
        CacheItemPoolInterface $cachePool,
        XmlCreateFeed $xmlCreateFeed,
        XmlRenderFeed $xmlRenderFeed,
        General $generalConfig,
        AddEpisodeToChannel $addEpisodeToChannel
    ) {
        $this->episodeApi          = $episodeApi;
        $this->cachePool           = $cachePool;
        $this->xmlCreateFeed       = $xmlCreateFeed;
        $this->xmlRenderFeed       = $xmlRenderFeed;
        $this->generalConfig       = $generalConfig;
        $this->addEpisodeToChannel = $addEpisodeToChannel;
    }

    public function generate(): string
    {
        $episodes = $this->getEpisodes();

        $lastPubDate = $this->getLastPubDate($episodes);

        $feed = $this->xmlCreateFeed->create();

        $channel = $this->addChannel(
            $feed,
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
    private function getEpisodes(): array
    {
        $fetchModel = new FetchModel();

        $fetchModel->orderByPublishedAt = true;

        $fetchModel->isPublished = true;

        $fetchModel->limit = 100;

        $episodes = $this->episodeApi->fetchEpisodes($fetchModel);

        foreach ($episodes as $key => $episode) {
            if ($episode->show->status !== ShowConstants::SHOW_STATUS_HIDDEN) {
                continue;
            }

            unset($episodes[$key]);
        }

        return $episodes;
    }

    /**
     * @param EpisodeModel[] $episodes
     *
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getLastPubDate(
        array $episodes
    ): DateTimeImmutable {
        if (count($episodes) < 1) {
            $cacheKey = 'feed_pub_date_master_feed';

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
        DateTimeImmutable $lastPubDate
    ): SimpleXMLElement {
        $feedUrl = $this->generalConfig->siteUrl() . '/masterfeed';

        $channel = $feed->addChild('channel');

        $atomLink = $channel->addChild(
            'atom:link',
            null,
            'http://www.w3.org/2005/Atom/'
        );
        $atomLink->addAttribute('href', $feedUrl);
        $atomLink->addAttribute('type', 'application/rss+xml');
        $atomLink->addAttribute('rel', 'self');

        $channel->addChild(
            'title',
            'Night Owl Master Feed '
        );

        $channel->addChild('link', $feedUrl);

        /** @noinspection PhpUnhandledExceptionInspection */
        $channel->addChild(
            'pubDate',
            $lastPubDate->format(DateTimeInterface::RFC2822),
        );

        $channel->addChild(
            'description',
            'The Night Owl master feed for all shows',
        );

        $channel->addChild(
            'itunes:summary',
            'The Night Owl master feed for all shows',
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $channel->addChild('language', 'en-US');

        $channel->addChild(
            'itunes:author',
            'Night Owl',
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $channel->addChild(
            'itunes:image',
            $this->generalConfig->siteUrl() . '/master-feed.jpg',
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $channel->addChild(
            'copyright',
            'Copyright ' .
            (new DateTimeImmutable())->format('Y') .
            ' BuzzingPixel, LLC'
        );

        return $channel;
    }
}
