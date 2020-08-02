<?php

declare(strict_types=1);

namespace Tests\Context\Users\Models;

use App\Context\Users\Models\UserModel;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

class UserModelTest extends TestCase
{
    public function testAsArray(): void
    {
        $model = new UserModel();

        $model->id = 'foo-id';

        $model->isAdmin = true;

        $model->emailAddress = 'foo-email';

        $model->passwordHash = 'foo-password-hash';

        $model->isActive = false;

        $model->timezone = new DateTimeZone('America/New_York');

        $createdAt = new DateTimeImmutable();

        $model->createdAt = $createdAt;

        self::assertSame(
            [
                'isAdmin' => true,
                'emailAddress' => 'foo-email',
                'isActive' => false,
                'timezone' => 'America/New_York',
                'createdAt' => $createdAt->format(
                    DateTimeInterface::ATOM
                ),
            ],
            $model->asArray()
        );

        self::assertSame(
            [
                'id' => 'foo-id',
                'isAdmin' => true,
                'emailAddress' => 'foo-email',
                'isActive' => false,
                'timezone' => 'America/New_York',
                'createdAt' => $createdAt->format(
                    DateTimeInterface::ATOM
                ),
            ],
            $model->asArray(false)
        );
    }
}
