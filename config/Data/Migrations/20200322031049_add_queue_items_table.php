<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
class AddQueueItemsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('queue_items', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('queue_id', 'uuid')
            ->addColumn('run_order', 'integer')
            ->addColumn(
                'is_finished',
                'boolean',
                ['default' => 0],
            )
            ->addColumn(
                'finished_at',
                'datetime',
                [
                    'null' => true,
                    'timezone' => true,
                ],
            )
            ->addColumn('class', 'string')
            ->addColumn('method', 'string')
            ->addColumn(
                'context',
                'json',
                ['null' => true]
            )
            ->create();
    }
}
