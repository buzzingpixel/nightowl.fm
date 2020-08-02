<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
class CreateQueueTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('queue', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('handle', 'string')
            ->addColumn('display_name', 'string')
            ->addColumn(
                'has_started',
                'boolean',
                ['default' => 0],
            )
            ->addColumn(
                'is_running',
                'boolean',
                ['default' => 0],
            )
            ->addColumn(
                'assume_dead_after',
                'datetime',
                ['timezone' => true],
            )
            ->addColumn(
                'initial_assume_dead_after',
                'datetime',
                ['timezone' => true],
            )
            ->addColumn(
                'is_finished',
                'boolean',
                ['default' => 0],
            )
            ->addColumn(
                'finished_due_to_error',
                'boolean',
                ['default' => 0],
            )
            ->addColumn(
                'error_message',
                'text',
                ['null' => true],
            )
            ->addColumn(
                'percent_complete',
                'float',
                ['default' => 0],
            )
            ->addColumn(
                'added_at',
                'datetime',
                ['timezone' => true],
            )
            ->addColumn(
                'finished_at',
                'datetime',
                [
                    'null' => true,
                    'timezone' => true,
                ],
            )
            ->create();
    }
}
