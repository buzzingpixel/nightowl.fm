<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Services\ResetTokenGarbageCollection;
use App\Persistence\Constants;
use App\Utilities\SystemClock;
use DateTimeZone;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use Throwable;

use function Safe\strtotime;

class ResetTokenGarbageCollectionTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test(): void
    {
        $dateTime = new DateTimeImmutable();

        $datetimeThirtyDaysAgo = (new DateTimeImmutable())
            ->setTimestamp(strtotime('2 hours ago'))
            ->setTimezone(new DateTimeZone('UTC'));

        $datetimeThirtyDaysAgoFormat = $datetimeThirtyDaysAgo->format(
            Constants::POSTGRES_OUTPUT_FORMAT
        );

        $systemClock = $this->createMock(SystemClock::class);

        $systemClock->expects(self::once())
            ->method('getCurrentTime')
            ->willReturn($dateTime);

        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::once())
            ->method('execute')
            ->with(self::equalTo(
                [$datetimeThirtyDaysAgoFormat]
            ))
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'DELETE FROM user_password_reset_tokens ' .
                ' WHERE created_at < ?'
            ))
            ->willReturn($statement);

        $service = new ResetTokenGarbageCollection(
            $pdo,
            $systemClock
        );

        $service();
    }
}
