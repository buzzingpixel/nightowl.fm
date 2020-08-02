<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\GeneratePasswordResetToken;
use App\Payload\Payload;
use App\Persistence\SaveNewRecord;
use App\Persistence\Users\UserPasswordResetTokenRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use stdClass;
use Tests\TestConfig;
use Throwable;

use function assert;

class GeneratePasswordResetTokenTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test(): void
    {
        $saveNewRecordArgHolder = new stdClass();

        $saveNewRecordArgHolder->record = null;

        $saveNewRecord = $this->createMock(
            SaveNewRecord::class
        );

        $saveNewRecord->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (UserPasswordResetTokenRecord $record) use (
                    $saveNewRecordArgHolder
                ): Payload {
                    $saveNewRecordArgHolder->record = $record;

                    return new Payload(Payload::STATUS_CREATED);
                }
            );

        $service = new GeneratePasswordResetToken(
            TestConfig::$di->get(
                UuidFactoryWithOrderedTimeCodec::class
            ),
            $saveNewRecord
        );

        $user     = new UserModel();
        $user->id = 'FooBarId';

        $payload = $service($user);

        self::assertSame(
            Payload::STATUS_CREATED,
            $payload->getStatus()
        );

        $record = $saveNewRecordArgHolder->record;

        /** @phpstan-ignore-next-line */
        assert(
        /** @phpstan-ignore-next-line */
            $record instanceof UserPasswordResetTokenRecord ||
            $record === null
        );

        self::assertInstanceOf(
            UserPasswordResetTokenRecord::class,
            $record
        );

        self::assertNotEmpty($record->id);

        self::assertSame('FooBarId', $record->user_id);

        $recordCreatedAt = DateTimeImmutable::createFromFormat(
            DateTimeInterface::ATOM,
            $record->created_at
        );

        $currentDate = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        self::assertSame(
            $currentDate->format('Y-m-d H:i:s'),
            $recordCreatedAt->format('Y-m-d H:i:s')
        );
    }
}
