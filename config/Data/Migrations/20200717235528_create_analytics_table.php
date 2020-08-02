<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

class CreateAnalyticsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('analytics', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('cookie_id', 'uuid')
            ->addColumn(
                'user_id',
                'uuid',
                ['null' => true]
            )
            ->addColumn(
                'logged_in_on_page_load',
                'boolean',
                ['default' => 0],
            )
            ->addColumn(
                'uri',
                'string',
                ['default' => '']
            )
            ->addColumn(
                'date',
                'datetime',
                ['timezone' => true]
            )
            ->create();
    }
}
