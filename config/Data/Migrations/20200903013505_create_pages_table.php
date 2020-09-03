<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class CreatePagesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('pages', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('title', 'string')
            ->addColumn('uri', 'text')
            ->addColumn('content', 'text')
            ->create();
    }
}
