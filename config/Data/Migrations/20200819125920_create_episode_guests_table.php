<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class CreateEpisodeGuestsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('episode_guests', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('episode_id', 'uuid')
            ->addColumn('person_id', 'uuid')
            ->create();
    }
}
