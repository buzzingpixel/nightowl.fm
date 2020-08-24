<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class CreateEpisodesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('episodes', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('show_id', 'uuid')
            ->addColumn('title', 'string')
            ->addColumn('status', 'string')
            ->addColumn('description', 'text')
            ->addColumn('file_location', 'string')
            ->addColumn('file_runtime_seconds', 'float')
            ->addColumn('file_size_bytes', 'string')
            ->addColumn('file_mime_type', 'string')
            ->addColumn('file_format', 'string')
            ->addColumn('episode_type', 'string')
            ->addColumn('explicit', 'boolean')
            ->addColumn('show_notes', 'text')
            ->addColumn(
                'publish_at',
                'datetime',
                [
                    'null' => true,
                    'timezone' => true,
                ]
            )
            ->addColumn('is_published', 'boolean')
            ->addColumn(
                'number',
                'integer',
                [
                    'null' => true,
                    'signed' => false,
                ]
            )
            ->addColumn(
                'display_order',
                'integer',
                ['signed' => false]
            )
            ->addColumn(
                'created_at',
                'datetime',
                ['timezone' => true]
            )
            ->create();
    }
}
