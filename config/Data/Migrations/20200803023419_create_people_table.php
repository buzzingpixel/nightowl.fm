<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
class CreatePeopleTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('people', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('first_name', 'string')
            ->addColumn('last_name', 'string')
            ->addColumn('slug', 'string')
            ->addColumn('email', 'string')
            ->addColumn('photo_file_location', 'string')
            ->addColumn('photo_preference', 'string')
            ->addColumn('bio', 'text')
            ->addColumn('location', 'string')
            ->addColumn('facebook_page_slug', 'string')
            ->addColumn('twitter_handle', 'string')
            ->addColumn('links', 'json')
            ->create();
    }
}
