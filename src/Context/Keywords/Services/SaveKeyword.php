<?php

declare(strict_types=1);

namespace App\Context\Keywords\Services;

use App\Context\Keywords\Models\KeywordModel;
use App\Persistence\Keywords\KeywordRecord;
use App\Persistence\RecordQueryFactory;
use App\Persistence\SaveNewRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;

use function assert;

class SaveKeyword
{
    private RecordQueryFactory $queryFactory;
    private SaveNewRecord $saveRecord;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;

    public function __construct(
        RecordQueryFactory $queryFactory,
        SaveNewRecord $saveRecord,
        UuidFactoryWithOrderedTimeCodec $uuidFactory
    ) {
        $this->queryFactory = $queryFactory;
        $this->saveRecord   = $saveRecord;
        $this->uuidFactory  = $uuidFactory;
    }

    public function save(KeywordModel $keyword): void
    {
        if ($keyword->id !== '') {
            return;
        }

        if ($this->checkIfKeywordExists($keyword)) {
            return;
        }

        $keyword->id = $this->uuidFactory->uuid1()->toString();

        $record = new KeywordRecord();

        $record->id = $keyword->id;

        $record->keyword = $keyword->keyword;

        $this->saveRecord->save($record);
    }

    private function checkIfKeywordExists(KeywordModel $keyword): bool
    {
        $record = $this->queryFactory->make(new KeywordRecord())
            ->withWhere('keyword', $keyword->keyword)
            ->one();

        assert($record instanceof KeywordRecord || $record === null);

        if ($record === null) {
            return false;
        }

        $keyword->id = $record->id;

        return true;
    }
}
