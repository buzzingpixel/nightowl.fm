<?php

declare(strict_types=1);

namespace App\Context\Feeds;

use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Keywords\Models\KeywordModel;
use App\Context\Series\Models\SeriesModel;
use App\Context\Shows\ShowApi;
use cebe\markdown\GithubMarkdown;
use DateTimeInterface;
use Safe\DateTimeImmutable;
use SimpleXMLElement;

use function array_map;
use function assert;
use function count;
use function htmlspecialchars;
use function implode;

class AddEpisodeToChannel
{
    private GetAuthorString $getAuthorString;
    private GithubMarkdown $markdown;
    private ShowApi $showApi;

    public function __construct(
        GetAuthorString $getAuthorString,
        GithubMarkdown $markdown,
        ShowApi $showApi
    ) {
        $this->getAuthorString = $getAuthorString;
        $this->markdown        = $markdown;
        $this->showApi         = $showApi;
    }

    public function add(
        SimpleXMLElement $channel,
        EpisodeModel $episode
    ): void {
        $item = $channel->addChild('item');

        $item->addChild(
            'title',
            htmlspecialchars($episode->getNumberedTitle())
        );

        $item->addChild('link', $episode->getPublicUrl());

        $guid = $item->addChild(
            'guid',
            $episode->getGuid(),
        );

        $guid->addAttribute('isPermalink', 'false');

        $publishedAt = $episode->publishedAt;

        assert($publishedAt instanceof DateTimeImmutable);

        /** @noinspection PhpUnhandledExceptionInspection */
        $item->addChild(
            'pubDate',
            $publishedAt->format(
                DateTimeInterface::RFC2822
            )
        );

        $authorString = htmlspecialchars(
            $this->getAuthorString->get($episode->hosts)
        );

        $item->addChild('author', $authorString);

        $item->addChild(
            'itunes:author',
            $authorString,
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $desc = htmlspecialchars($episode->description);

        $item->addChild('description', $desc);

        $item->addChild(
            'itunes:summary',
            $desc,
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

        $item->addChild(
            'itunes:duration',
            $episode->getFeedRunTime(),
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
                htmlspecialchars(
                    implode(', ', array_map(
                        static fn (KeywordModel $k) => $k->keyword,
                        $episode->keywords,
                    ))
                ),
                'http://www.itunes.com/dtds/podcast-1.0.dtd',
            );
        }

        $imageUrl = $this->showApi->getShowArtworkUrlPublic(
            $episode->show,
            ['size' => 1400]
        );

        $image = $item->addChild('image');

        $image->addChild('url', $imageUrl);

        $itunesImage = $item->addChild(
            'itunes:image',
            '',
            'http://www.itunes.com/dtds/podcast-1.0.dtd',
        );

        $itunesImage->addAttribute('href', $imageUrl);
    }
}
