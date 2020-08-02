<?php

declare(strict_types=1);

namespace Tests\Context\Queue\Models;

use App\Context\Queue\Models\QueueItemModel;
use App\Context\Queue\Models\QueueModel;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

class QueueModelTest extends TestCase
{
    public function testMagicSetWhenInvalidProperty(): void
    {
        $model = new QueueModel();

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage('Property does not exist');

        $model->foo = 'bar';
    }

    public function testMagicSetWhenInvalidItems(): void
    {
        $model = new QueueModel();

        $exception = null;

        try {
            $model->items = ['bar'];
        } catch (Throwable $e) {
            $exception = $e;
        }

        self::assertInstanceOf(Throwable::class, $exception);
    }

    public function testMagicGetWhenInvalidProperty(): void
    {
        $model = new QueueModel();

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage('Property does not exist');

        $model->foo;
    }

    public function testMagicGetSet(): void
    {
        $item = new QueueItemModel();

        $model = new QueueModel();

        $model->items = [$item];

        self::assertSame([$item], $model->items);
    }

    public function testIsset(): void
    {
        $model = new QueueModel();

        self::assertFalse(isset($model->foo));

        self::assertTrue(isset($model->items));
    }
}
