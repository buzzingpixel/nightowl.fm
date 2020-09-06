<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class CreateEpisodeDownloadStatsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('episode_downloads_stats', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('episode_id', 'uuid')
            ->addColumn('total_downloads', 'integer')
            ->addColumn('downloads_past_thirty_days', 'integer')
            ->addColumn('downloads_past_sixty_days', 'integer')
            ->addColumn('downloads_past_year', 'integer')
            ->create();
    }
}
