<?php

declare(strict_types=1);

use App\Globals;
use App\Persistence\Keywords\KeywordRecord;
use App\Persistence\RecordQueryFactory;
use App\Persistence\SaveExistingRecord;
use Cocur\Slugify\Slugify;
use Phinx\Migration\AbstractMigration;

/** @noinspection AutoloadingIssuesInspection */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ClassFileName.NoMatch

/** @noinspection PhpIllegalPsrClassPathInspection */
final class ResaveKeywords extends AbstractMigration
{
    public function up(): void
    {
        /** @var KeywordRecord[] $records */
        $records = Globals::di()->get(RecordQueryFactory::class)
            ->make(new KeywordRecord())
            ->all();

        if (count($records) < 1) {
            return;
        }

        $saveRecord = Globals::di()->get(SaveExistingRecord::class);

        $slugify = new Slugify();

        foreach ($records as $record) {
            $record->slug = $slugify->slugify($record->keyword);

            $saveRecord->save($record);
        }
    }

    public function down(): void
    {
    }
}
