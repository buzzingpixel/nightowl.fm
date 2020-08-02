<?php

declare(strict_types=1);

namespace Tests\Persistence;

use App\Persistence\RecordQueryFactory;
use App\Persistence\Schedule\ScheduleTrackingRecord;
use InvalidArgumentException;
use LogicException;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecordQueryTest extends TestCase
{
    /** @var PDO&MockObject */
    private $pdo;
    private RecordQueryFactory $factory;

    public function testInitial(): void
    {
        $recordQuery = ($this->factory)(
            new ScheduleTrackingRecord()
        );

        $sqlAndBind = $recordQuery->getSqlAndBind();

        self::assertCount(2, $sqlAndBind);

        self::assertSame(
            'SELECT * FROM schedule_tracking;',
            $sqlAndBind['sql']
        );

        self::assertSame([], $sqlAndBind['bind']);
    }

    public function testDoubleInstantiation(): void
    {
        $recordQuery = ($this->factory)(
            new ScheduleTrackingRecord()
        );

        $this->expectException(LogicException::class);

        $this->expectExceptionMessage(
            'RecordQuery can only be instantiated once'
        );

        $recordQuery->__construct(
            new ScheduleTrackingRecord(),
            $this->pdo
        );
    }

    public function testWithWhere(): void
    {
        $recordQuery = ($this->factory)(
            new ScheduleTrackingRecord()
        );

        $newQuery = $recordQuery->withWhere(
            'fooCol',
            'fooVal',
            '>'
        );

        self::assertNotSame($recordQuery, $newQuery);

        $sqlAndBind = $newQuery->getSqlAndBind();

        self::assertSame(
            'SELECT * FROM schedule_tracking WHERE (fooCol > :fooCol_1);',
            $sqlAndBind['sql']
        );

        self::assertSame(
            [':fooCol_1' => 'fooVal'],
            $sqlAndBind['bind']
        );
    }

    public function testWithLimit(): void
    {
        $recordQuery = ($this->factory)(
            new ScheduleTrackingRecord()
        );

        $newQuery = $recordQuery->withLimit(23);

        self::assertNotSame($recordQuery, $newQuery);

        $sqlAndBind = $newQuery->getSqlAndBind();

        self::assertSame(
            'SELECT * FROM schedule_tracking LIMIT 23;',
            $sqlAndBind['sql']
        );

        self::assertSame(
            [],
            $sqlAndBind['bind']
        );
    }

    public function testWithOffset(): void
    {
        $recordQuery = ($this->factory)(
            new ScheduleTrackingRecord()
        );

        $newQuery = $recordQuery->withOffset(3);

        self::assertNotSame($recordQuery, $newQuery);

        $sqlAndBind = $newQuery->getSqlAndBind();

        self::assertSame(
            'SELECT * FROM schedule_tracking OFFSET 3;',
            $sqlAndBind['sql']
        );

        self::assertSame(
            [],
            $sqlAndBind['bind']
        );
    }

    public function testNotNull(): void
    {
        $recordQuery = ($this->factory)(
            new ScheduleTrackingRecord()
        );

        $newQuery = $recordQuery->withWhere(
            'asdf_col',
            'null',
            '!=',
        );

        self::assertNotSame($recordQuery, $newQuery);

        $sqlAndBind = $newQuery->getSqlAndBind();

        self::assertSame(
            'SELECT * FROM schedule_tracking WHERE (asdf_col IS NOT NULL);',
            $sqlAndBind['sql']
        );

        self::assertSame(
            [],
            $sqlAndBind['bind']
        );
    }

    public function testIsNull(): void
    {
        $recordQuery = ($this->factory)(
            new ScheduleTrackingRecord()
        );

        $newQuery = $recordQuery->withWhere(
            'asdf_col',
            'null',
        );

        self::assertNotSame($recordQuery, $newQuery);

        $sqlAndBind = $newQuery->getSqlAndBind();

        self::assertSame(
            'SELECT * FROM schedule_tracking WHERE (asdf_col IS NULL);',
            $sqlAndBind['sql']
        );

        self::assertSame(
            [],
            $sqlAndBind['bind']
        );
    }

    public function testWithWhereGroup(): void
    {
        $query = ($this->factory)(new ScheduleTrackingRecord())
            ->withWhere('baz_bar', 'foo_baz', 'IN')
            ->withWhere('foo_col', 'foo_val', '!=')
            ->withWhere(
                'bar_col',
                [
                    'bar_val_arr_1',
                    'bar_val_arr_2',
                    'bar_val_arr_3',
                ],
                'IN'
            )
            ->withNewWhereGroup('baz_col', 'baz_val', '!=')
            ->withWhere('foo_bar_col', 'foo_bar_val', 'IN');

        $sqlAndBind = $query->getSqlAndBind();

        self::assertSame(
            'SELECT * FROM schedule_tracking WHERE ' .
            '(baz_bar IN (:baz_bar_1) AND foo_col != :foo_col_2 AND ' .
            'bar_col IN (:bar_col_3,:bar_col_4,:bar_col_5)) OR ' .
            '(baz_col != :baz_col_6 AND foo_bar_col IN (:foo_bar_col_7));',
            $sqlAndBind['sql']
        );

        self::assertSame(
            [
                ':baz_bar_1' => 'foo_baz',
                ':foo_col_2' => 'foo_val',
                ':bar_col_3' => 'bar_val_arr_1',
                ':bar_col_4' => 'bar_val_arr_2',
                ':bar_col_5' => 'bar_val_arr_3',
                ':baz_col_6' => 'baz_val',
                ':foo_bar_col_7' => 'foo_bar_val',
            ],
            $sqlAndBind['bind']
        );
    }

    public function testInvalidWithOrder(): void
    {
        self::expectException(InvalidArgumentException::class);

        self::expectExceptionMessage(
            'Direction must be asc or desc'
        );

        ($this->factory)(new ScheduleTrackingRecord())->withOrder(
            'foo_col',
            'foo_dir',
        );
    }

    public function testWithOrder(): void
    {
        $query = ($this->factory)(new ScheduleTrackingRecord())
            ->withWhere('foo_col', 'foo_val')
            ->withOrder('foo_col', 'asc')
            ->withOrder('bar_col', 'desc')
            ->withOrder('baz_col');

        $sqlAndBind = $query->getSqlAndBind();

        self::assertSame(
            'SELECT * FROM schedule_tracking WHERE ' .
            '(foo_col = :foo_col_1) ORDER BY foo_col ASC, ' .
            'bar_col DESC, baz_col DESC;',
            $sqlAndBind['sql']
        );

        self::assertSame(
            [':foo_col_1' => 'foo_val'],
            $sqlAndBind['bind']
        );
    }

    public function testOneWithNoRecordReturn(): void
    {
        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::at(0))
            ->method('execute')
            ->with(self::equalTo(
                [':foo_col_1' => 'foo_val']
            ))
            ->willReturn(true);

        $statement->expects(self::at(1))
            ->method('fetchObject')
            ->with(self::equalTo(
                ScheduleTrackingRecord::class
            ))
            ->willReturn(false);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT * FROM schedule_tracking WHERE (foo_col = :foo_col_1);'
            ))
            ->willReturn($statement);

        $query = ($this->factory)(new ScheduleTrackingRecord())
            ->withWhere('foo_col', 'foo_val');

        self::assertNull($query->one());
    }

    public function testOne(): void
    {
        $record = new ScheduleTrackingRecord();

        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::at(0))
            ->method('execute')
            ->with(self::equalTo(
                [':foo_col_1' => 'foo_val']
            ))
            ->willReturn(true);

        $statement->expects(self::at(1))
            ->method('fetchObject')
            ->with(self::equalTo(
                ScheduleTrackingRecord::class
            ))
            ->willReturn($record);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT * FROM schedule_tracking WHERE (foo_col = :foo_col_1);'
            ))
            ->willReturn($statement);

        $query = ($this->factory)(new ScheduleTrackingRecord())
            ->withWhere('foo_col', 'foo_val');

        self::assertSame($record, $query->one());
    }

    public function testAll(): void
    {
        $records = [
            new ScheduleTrackingRecord(),
            new ScheduleTrackingRecord(),
        ];

        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::at(0))
            ->method('execute')
            ->with(self::equalTo([]))
            ->willReturn(true);

        $statement->expects(self::at(1))
            ->method('fetchAll')
            ->with(
                self::equalTo(PDO::FETCH_CLASS),
                self::equalTo(ScheduleTrackingRecord::class)
            )
            ->willReturn($records);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT * FROM schedule_tracking;'
            ))
            ->willReturn($statement);

        $query = ($this->factory)(new ScheduleTrackingRecord());

        self::assertSame($records, $query->all());
    }

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);

        $this->factory = new RecordQueryFactory($this->pdo);
    }
}
