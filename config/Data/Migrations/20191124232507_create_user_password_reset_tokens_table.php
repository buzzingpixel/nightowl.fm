<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
class CreateUserPasswordResetTokensTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('user_password_reset_tokens', [
            'id' => false,
            'primary_key' => ['id'],
        ])
            ->addColumn('id', 'uuid')
            ->addColumn('user_id', 'uuid')
            ->addColumn(
                'created_at',
                'datetime',
                ['timezone' => true]
            )
            ->create();
    }
}
