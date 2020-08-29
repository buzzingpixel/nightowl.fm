<?php

declare(strict_types=1);

use App\Context\PodcastCategories\Services\SyncWithCsv;
use App\Globals;
use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class PopulatePodcastCategoriesTable extends AbstractMigration
{
    /**
     * @throws Throwable
     */
    public function up(): void
    {
        Globals::di()->get(SyncWithCsv::class)->sync();
    }

    public function down(): void
    {
        Globals::di()->get(PDO::class)->query(
            'TRUNCATE TABLE podcast_categories'
        );
    }
}
