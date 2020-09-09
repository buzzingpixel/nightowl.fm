<?php

declare(strict_types=1);

namespace App\Context\Analytics\Models;

use App\Context\Users\Models\UserModel;
use buzzingpixel\cookieapi\interfaces\CookieInterface;
use DateTimeImmutable;
use DateTimeZone;

class AnalyticsModel
{
    public function __construct()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->date = new \Safe\DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );
    }

    /** @psalm-suppress PropertyNotSetInConstructor */
    public CookieInterface $cookie;

    public ?UserModel $user = null;

    public bool $wasLoggedInOnPageLoad = false;

    public string $uri = '';

    public DateTimeImmutable $date;
}
