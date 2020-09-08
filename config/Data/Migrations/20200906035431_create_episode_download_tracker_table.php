<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class CreateEpisodeDownloadTrackerTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('episode_download_tracker', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('episode_id', 'uuid')
            ->addColumn(
                'is_full_range',
                'boolean',
                ['default' => 0],
            )
            ->addColumn('range_start', 'integer')
            ->addColumn('range_end', 'integer')
            ->addColumn(
                'downloaded_at',
                'datetime',
                ['timezone' => true]
            )
            ->create();
    }
}
