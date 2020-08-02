<?php

declare(strict_types=1);

namespace App\Context\Users\Models;

use DateTimeInterface;
use DateTimeZone;
use Safe\DateTimeImmutable;

class UserModel
{
    public function __construct()
    {
        $this->timezone = new DateTimeZone('US/Central');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->createdAt = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );
    }

    public string $id = '';

    public bool $isAdmin = false;

    public string $emailAddress = '';

    public string $passwordHash = '';

    public string $newPassword = '';

    public bool $isActive = true;

    public DateTimeZone $timezone;

    public DateTimeImmutable $createdAt;

    /**
     * @return mixed[]
     */
    public function asArray(bool $excludeId = true): array
    {
        $array = [];

        if (! $excludeId) {
            $array['id'] = $this->id;
        }

        $array['isAdmin'] = $this->isAdmin;

        $array['emailAddress'] = $this->emailAddress;

        // Lets not put this in the array for now
        // $array['passwordHash'] = $this->passwordHash;

        $array['isActive'] = $this->isActive;

        $array['timezone'] = $this->timezone->getName();

        $array['createdAt'] = $this->createdAt->format(
            DateTimeInterface::ATOM
        );

        return $array;
    }
}
