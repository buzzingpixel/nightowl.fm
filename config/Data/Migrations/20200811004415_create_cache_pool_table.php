<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class CreateCachePoolTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('cache_pool', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('key', 'text')
            ->addColumn('value', 'text')
            ->addColumn(
                'expires_at',
                'datetime',
                [
                    'null' => true,
                    'timezone' => true,
                ]
            )
            ->create();
    }
}
