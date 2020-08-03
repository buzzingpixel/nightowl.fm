<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
class CreateShowsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shows', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('title', 'string')
            ->addColumn('slug', 'string')
            ->addColumn('artwork_file_location', 'string')
            ->addColumn('status', 'string')
            ->addColumn('description', 'text')
            ->addColumn('explicit', 'boolean')
            ->addColumn('itunes_link', 'string')
            ->addColumn('google_play_link', 'string')
            ->addColumn('stitcher_link', 'string')
            ->addColumn('spotify_link', 'string')
            ->create();
    }
}
