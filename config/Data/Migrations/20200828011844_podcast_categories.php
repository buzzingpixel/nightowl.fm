<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class PodcastCategories extends AbstractMigration
{
    public function change(): void
    {
        $this->table('podcast_categories', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('parent_id', 'uuid')
            ->addColumn('parent_chain', 'json')
            ->addColumn('name', 'string')
            ->create();
    }
}
