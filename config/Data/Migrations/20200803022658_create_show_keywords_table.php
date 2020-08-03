<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
class CreateShowKeywordsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('show_keywords', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('show_id', 'uuid')
            ->addColumn('keyword_id', 'uuid')
            ->create();
    }
}
