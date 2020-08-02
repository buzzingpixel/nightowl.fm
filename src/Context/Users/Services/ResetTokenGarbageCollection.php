<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Persistence\Constants;
use App\Utilities\SystemClock;
use DateTimeZone;
use PDO;
use Throwable;

use function Safe\strtotime;

class ResetTokenGarbageCollection
{
    private PDO $pdo;
    private SystemClock $systemClock;

    public function __construct(PDO $pdo, SystemClock $systemClock)
    {
        $this->pdo         = $pdo;
        $this->systemClock = $systemClock;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(): void
    {
        $datetime = $this->systemClock->getCurrentTime()
            ->setTimestamp(
                strtotime('2 hours ago')
            )
            ->setTimezone(new DateTimeZone('UTC'));

        $format = $datetime->format(Constants::POSTGRES_OUTPUT_FORMAT);

        $statement = $this->pdo->prepare(
            'DELETE FROM user_password_reset_tokens ' .
            ' WHERE created_at < ?'
        );

        $statement->execute([$format]);
    }
}
