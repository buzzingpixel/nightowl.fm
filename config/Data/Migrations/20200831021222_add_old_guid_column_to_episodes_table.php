<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class AddOldGuidColumnToEpisodesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('episodes')
            ->addColumn(
                'old_guid',
                'string',
                ['default' => '']
            )
            ->update();
    }
}
