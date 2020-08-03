<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
class CreateShowHostsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('show_hosts', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('show_id', 'uuid')
            ->addColumn('person_id', 'uuid')
            ->create();
    }
}
