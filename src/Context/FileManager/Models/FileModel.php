<?php

declare(strict_types=1);

namespace App\Context\FileManager\Models;

use DateTimeZone;
use Safe\DateTimeImmutable;

use function number_format;

class FileModel
{
    public function __construct()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->dateUpdated = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC'),
        );
    }

    public string $path = '';

    public DateTimeImmutable $dateUpdated;

    public int $bytes = 0;

    public string $dirName = '';

    public string $baseName = '';

    public string $extension = '';

    public string $fileName = '';

    public string $publicUrl = '';

    public function megabytes(): float
    {
        return $this->bytes / 1048576;
    }

    public function kilobytes(): float
    {
        return $this->bytes / 1024;
    }

    public function humanReadableSize(): string
    {
        $mb = $this->megabytes();

        if ($this->megabytes() >= 1) {
            return number_format(
                $mb,
                1,
                '.',
                ',',
            ) . 'mb';
        }

        $kb = $this->kilobytes();

        if ($this->kilobytes() >= 1) {
            return number_format(
                $kb,
                1,
                '.',
                ',',
            ) . 'kb';
        }

        return number_format(
            $this->bytes,
            0,
            '.',
            ',',
        ) . 'b';
    }
}
