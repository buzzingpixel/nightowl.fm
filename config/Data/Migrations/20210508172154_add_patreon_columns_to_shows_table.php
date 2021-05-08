<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch
// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

/** @noinspection PhpIllegalPsrClassPathInspection */
final class AddPatreonColumnsToShowsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shows')
            ->addColumn(
                'patreon_link',
                'string',
                ['default' => ''],
            )
            ->addColumn(
                'patreon_cta',
                'string',
                ['default' => ''],
            )
            ->addColumn(
                'patreon_headline',
                'string',
                ['default' => ''],
            )
            ->addColumn(
                'patreon_description',
                'text',
                ['default' => ''],
            )
            ->update();
    }
}
