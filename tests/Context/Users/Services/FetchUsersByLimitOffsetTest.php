<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Services\FetchUsersByLimitOffset;
use App\Context\Users\Transformers\TransformUserRecordToUserModel;
use App\Persistence\RecordQuery;
use App\Persistence\RecordQueryFactory;
use App\Persistence\Users\UserRecord;
use PHPUnit\Framework\TestCase;
use Tests\TestConfig;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchUsersByLimitOffsetTest extends TestCase
{
    public function test(): void
    {
        $record1             = new UserRecord();
        $record1->timezone   = 'America/Chicago';
        $record1->created_at = '2020-01-24 19:59:38+00';

        $record2             = new UserRecord();
        $record2->timezone   = 'America/Chicago';
        $record2->created_at = '2020-01-24 19:59:38+00';

        $recordQuery = $this->createMock(RecordQuery::class);

        $recordQuery->expects(self::at(0))
            ->method('withOrder')
            ->with(
                self::equalTo('last_name'),
                self::equalTo('asc')
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(1))
            ->method('withOrder')
            ->with(
                self::equalTo('first_name'),
                self::equalTo('asc')
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(2))
            ->method('withOrder')
            ->with(
                self::equalTo('email_address'),
                self::equalTo('asc')
            )
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(3))
            ->method('withLimit')
            ->with(self::equalTo(14))
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(4))
            ->method('withOffset')
            ->with(self::equalTo(45))
            ->willReturn($recordQuery);

        $recordQuery->expects(self::at(5))
            ->method('all')
            ->willReturn([
                $record1,
                $record2,
            ]);

        $recordQueryFactory = $this->createMock(
            RecordQueryFactory::class
        );

        $recordQueryFactory->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (UserRecord $record) use (
                    $recordQuery
                ) {
                    return $recordQuery;
                }
            );

        $service = new FetchUsersByLimitOffset(
            $recordQueryFactory,
            TestConfig::$di->get(
                TransformUserRecordToUserModel::class
            )
        );

        $models = $service(14, 45);

        self::assertCount(2, $models);
    }
}
