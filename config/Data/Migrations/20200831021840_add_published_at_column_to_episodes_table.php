<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class AddPublishedAtColumnToEpisodesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('episodes')
            ->addColumn(
                'published_at',
                'datetime',
                [
                    'null' => true,
                    'timezone' => true,
                ]
            )
            ->update();
    }
}
