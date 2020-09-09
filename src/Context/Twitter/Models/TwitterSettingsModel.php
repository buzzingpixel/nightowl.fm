<?php

declare(strict_types=1);

namespace App\Context\Twitter\Models;

use function is_array;
use function Safe\json_decode;
use function Safe\json_encode;

class TwitterSettingsModel
{
    /**
     * @param mixed[] $properties
     */
    public function __construct(array $properties = [])
    {
        $this->settingId             = (string) ($properties['settingId'] ?? '');
        $this->twitterAuth           = (bool) ($properties['twitterAuth'] ?? false);
        $this->twitterConsumerKey    = (string) ($properties['twitterConsumerKey'] ?? '');
        $this->twitterConsumerSecret = (string) ($properties['twitterConsumerSecret'] ?? '');
        $this->twitterOathToken      = (string) ($properties['twitterOathToken'] ?? '');
        $this->twitterOathSecret     = (string) ($properties['twitterOathSecret'] ?? '');
        $this->twitterUserId         = (string) ($properties['twitterUserId'] ?? '');
        $this->twitterScreenName     = (string) ($properties['twitterScreenName'] ?? '');
    }

    public string $settingId = '';

    public static string $settingKey = 'twitterSettings';

    public bool $twitterAuth = false;

    public string $twitterConsumerKey = '';

    public string $twitterConsumerSecret = '';

    public string $twitterOathToken = '';

    public string $twitterOathSecret = '';

    public string $twitterUserId = '';

    public string $twitterScreenName = '';

    /**
     * @param mixed[] $properties
     */
    public static function fromArray(array $properties): self
    {
        return new self($properties);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public static function fromJson(string $json): self
    {
        /** @psalm-suppress MixedAssignment */
        $jsonArray = json_decode($json, true);

        return self::fromArray(
            is_array($jsonArray) ? $jsonArray : []
        );
    }

    /**
     * @return mixed[]
     */
    public function asArray(): array
    {
        return [
            'settingId' => $this->settingId,
            'twitterAuth' => $this->twitterAuth,
            'twitterConsumerKey' => $this->twitterConsumerKey,
            'twitterConsumerSecret' => $this->twitterConsumerSecret,
            'twitterOathToken' => $this->twitterOathToken,
            'twitterOathSecret' => $this->twitterOathSecret,
            'twitterUserId' => $this->twitterUserId,
            'twitterScreenName' => $this->twitterScreenName,
        ];
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function asJson(): string
    {
        return json_encode($this->asArray());
    }
}
