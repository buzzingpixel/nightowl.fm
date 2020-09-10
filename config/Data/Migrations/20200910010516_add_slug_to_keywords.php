<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class AddSlugToKeywords extends AbstractMigration
{
    public function change(): void
    {
        $this->table('keywords')
            ->addColumn(
                'slug',
                'string',
                ['default' => ''],
            )
            ->update();
    }
}
