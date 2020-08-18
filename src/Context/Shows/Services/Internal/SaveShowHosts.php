<?php

declare(strict_types=1);

namespace App\Context\Shows\Services\Internal;

use App\Context\People\Models\PersonModel;
use App\Context\Shows\Models\ShowModel;
use App\Persistence\RecordQueryFactory;
use App\Persistence\SaveNewRecord;
use App\Persistence\Shows\ShowHostsRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;

use function array_map;
use function array_walk;
use function count;
use function dd;
use function in_array;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class SaveShowHosts
{
    private RecordQueryFactory $recordQueryFactory;
    private SaveNewRecord $saveNewRecord;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;

    public function __construct(
        RecordQueryFactory $recordQueryFactory,
        SaveNewRecord $saveNewRecord,
        UuidFactoryWithOrderedTimeCodec $uuidFactory
    ) {
        $this->recordQueryFactory = $recordQueryFactory;
        $this->saveNewRecord      = $saveNewRecord;
        $this->uuidFactory        = $uuidFactory;
    }

    public function save(ShowModel $show): void
    {
        /** @var ShowHostsRecord[] $allPreviousHosts */
        $allPreviousHosts = $this->recordQueryFactory
            ->make(new ShowHostsRecord())
            ->withWhere('show_id', $show->id)
            ->all();

        $this->deleteNonExisting(
            $allPreviousHosts,
            $show
        );

        $this->insertNew(
            $allPreviousHosts,
            $show
        );
    }

    /**
     * @param ShowHostsRecord[] $allPreviousHosts
     */
    private function deleteNonExisting(
        array $allPreviousHosts,
        ShowModel $show
    ): void {
        if (count($allPreviousHosts) < 1) {
            return;
        }

        // TODO: Implement deleteNonExisting method
        dd('TODO: Implement deleteNonExisting method');
    }

    /**
     * @param ShowHostsRecord[] $allPreviousHosts
     */
    private function insertNew(
        array $allPreviousHosts,
        ShowModel $show
    ): void {
        $newShowHosts = $show->hosts;

        if (count($newShowHosts) < 1) {
            return;
        }

        $existingIds = array_map(
            static fn (ShowHostsRecord $r) => $r->id,
            $allPreviousHosts,
        );

        array_walk(
            $newShowHosts,
            function (
                PersonModel $host
            ) use (
                $existingIds,
                $show
            ): void {
                if (in_array($host->id, $existingIds)) {
                    return;
                }

                $record = new ShowHostsRecord();

                $record->id = $this->uuidFactory->uuid1()->toString();

                $record->show_id = $show->id;

                $record->person_id = $host->id;

                $this->saveNewRecord->save($record);
            }
        );
    }
}
