<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
class CreateScheduleTrackingTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('schedule_tracking', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid',)
            ->addColumn('class', 'text')
            ->addColumn('is_running', 'boolean', [
                'default' => 0,
                'comment' => 'Whether the scheduled task is running',
            ])
            ->addColumn(
                'last_run_start_at',
                'datetime',
                ['timezone' => true]
            )
            ->addColumn(
                'last_run_end_at',
                'datetime',
                ['timezone' => true]
            )
            ->create();
    }
}
