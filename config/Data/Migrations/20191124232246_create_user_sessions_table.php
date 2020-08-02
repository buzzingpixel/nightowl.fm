<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
class CreateUserSessionsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('user_sessions', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('user_id', 'uuid',)
            ->addColumn(
                'created_at',
                'datetime',
                ['timezone' => true]
            )
            ->addColumn(
                'last_touched_at',
                'datetime',
                ['timezone' => true]
            )
            ->create();
    }
}
