<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
class CreateItunesCategoriesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('itunes_categories', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('category', 'string')
            ->addColumn('parent_id', 'uuid')
            ->addColumn('order', 'integer', ['signed' => false])
            ->create();
    }
}
