<?php

declare(strict_types=1);

namespace App\Context\Episodes\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Context\Episodes\EpisodeConstants;
use App\Context\Episodes\Models\EpisodeModel;
use App\Context\Twitter\TwitterApi;
use Config\General;

use function mb_strlen;
use function mb_substr;

class TweetEpisode
{
    private General $generalConfig;
    private TwitterApi $twitterApi;

    public function __construct(
        General $generalConfig,
        TwitterApi $twitterApi
    ) {
        $this->generalConfig = $generalConfig;
        $this->twitterApi    = $twitterApi;
    }

    public function tweet(EpisodeModel $episode): void
    {
        if (
            ! $this->generalConfig->tweetNewEpisodes() ||
            $episode->status !== EpisodeConstants::EPISODE_STATUS_LIVE
        ) {
            return;
        }

        $settings = $this->twitterApi->fetchTwitterSettings();

        if (! $settings->twitterAuth) {
            return;
        }

        $connection = new TwitterOAuth(
            $settings->twitterConsumerKey,
            $settings->twitterConsumerSecret,
            $settings->twitterOathToken,
            $settings->twitterOathSecret
        );

        $title = $episode->getNumberedTitleWithShow();

        $url = $episode->getPublicUrl();

        // Twitter character limit is 280. All URLs are 23 characters plus
        // the space
        $charLimit = 280 - 24;

        if (mb_strlen($title) > $charLimit) {
            $title = mb_substr($title, 0, $charLimit - 1) . '…';
        }

        $tweet = $title . ' ' . $url;

        $connection->post(
            'statuses/update',
            ['status' => $tweet]
        );
    }
}
