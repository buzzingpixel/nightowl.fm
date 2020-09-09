<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class CreateSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('settings', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('key', 'string')
            ->addColumn(
                'value',
                'json',
                ['null' => true],
            )
            ->create();
    }
}
