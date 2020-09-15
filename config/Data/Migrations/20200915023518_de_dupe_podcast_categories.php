<?php

declare(strict_types=1);

use App\Context\PodcastCategories\PodcastCategoriesApi;
use App\Globals;
use App\Persistence\PodcastCategories\PodcastCategoryRecord;
use App\Persistence\Shows\ShowPodcastCategoriesRecord;
use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch
// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

/** @noinspection PhpIllegalPsrClassPathInspection */
final class DeDupePodcastCategories extends AbstractMigration
{
    /**
     * @throws Throwable
     */
    public function up(): void
    {
        $pdo = Globals::di()->get(PDO::class);

        $categoriesApi = Globals::di()->get(PodcastCategoriesApi::class);

        $categories = $categoriesApi->fetchCategories();

        foreach ($categories as $category) {
            $statement = $pdo->prepare(
                'DELETE FROM ' .
                PodcastCategoryRecord::tableName() .
                ' WHERE id=:id'
            );

            $params = [':id' => $category->id];

            if (! $statement->execute($params)) {
                throw new Exception('Unable to delete record');
            }

            $statement = $pdo->prepare(
                'DELETE FROM ' .
                ShowPodcastCategoriesRecord::tableName() .
                ' WHERE podcast_category_id=:podcast_category_id'
            );

            $params = [':podcast_category_id' => $category->id];

            if (! $statement->execute($params)) {
                throw new Exception('Unable to delete record');
            }
        }
    }

    public function down(): void
    {
    }
}
