<?php

declare(strict_types=1);

namespace App\Context\PodcastCategories\Services;

use Adbar\Dot;
use App\Context\PodcastCategories\Models\PodcastCategoryModel;
use App\Context\PodcastCategories\PodcastCategoryConstants;
use App\Persistence\PodcastCategories\PodcastCategoryRecord;
use App\Persistence\RecordQueryFactory;
use App\Persistence\SaveNewRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use League\Csv\Reader;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use PDO;
use Throwable;

use function array_map;
use function explode;
use function implode;
use function in_array;
use function Safe\json_decode;
use function Safe\json_encode;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class SyncWithCsv
{
    private Filesystem $filesystem;
    private UuidFactoryWithOrderedTimeCodec $uuidFactory;
    private RecordQueryFactory $recordQueryFactory;
    private SaveNewRecord $saveNewRecord;
    private PDO $pdo;

    public function __construct(
        Filesystem $filesystem,
        UuidFactoryWithOrderedTimeCodec $uuidFactory,
        RecordQueryFactory $recordQueryFactory,
        SaveNewRecord $saveNewRecord,
        PDO $pdo
    ) {
        $this->filesystem         = $filesystem;
        $this->uuidFactory        = $uuidFactory;
        $this->recordQueryFactory = $recordQueryFactory;
        $this->saveNewRecord      = $saveNewRecord;
        $this->pdo                = $pdo;
    }

    /** @var PodcastCategoryRecord[] */
    private array $allRecordsByPathKey = [];

    /** @var string[] */
    private array $allCsvItemKeys = [];

    /**
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function sync(): void
    {
        $this->populateRecordsByKeyPath();

        $csvStr = $this->filesystem->read(
            PodcastCategoryConstants::CSV_FILE_PATH
        );

        $csv = Reader::createFromString($csvStr);

        $csv->setHeaderOffset(0);

        $items = new Dot();

        foreach ($csv as $item) {
            $key = $item['Parent Chain'] ?: '';

            $key = $key ? $key . '/' : '';

            $key .= $item['Category'];

            $this->allCsvItemKeys[] = $key;

            $keyDots = implode(
                '.children.',
                explode(
                    '/',
                    $key
                ),
            );

            $items->add($keyDots, [
                'name' => $item['Category'],
                'children' => [],
            ]);

            $keyedItems[$key] = $item['Category'];
        }

        foreach ($items->all() as $item) {
            $this->processItem($item);
        }

        foreach ($this->allRecordsByPathKey as $key => $record) {
            if (in_array($key, $this->allCsvItemKeys)) {
                continue;
            }

            $statement = $this->pdo->prepare(
                'DELETE FROM ' .
                PodcastCategoryRecord::tableName() .
                ' WHERE id=:id'
            );

            $statement->execute([':id' => $record->id]);
        }
    }

    /**
     * @param mixed[] $item
     *
     * @throws Throwable
     */
    private function processItem(
        array $item,
        ?PodcastCategoryModel $parent = null
    ): void {
        $podcastModel = new PodcastCategoryModel();

        if ($parent !== null) {
            $podcastModel = new PodcastCategoryModel(
                $parent->getParentChainWithSelf(),
            );
        }

        $podcastModel->name = $item['name'];

        $chainPath = $podcastModel->getParentChainWithSelfAsPath();

        if (isset($this->allRecordsByPathKey[$chainPath])) {
            return;
        }

        $podcastModel->id = $this->uuidFactory->uuid1()->toString();

        $record = new PodcastCategoryRecord();

        $record->id = $podcastModel->id;

        $record->name = $podcastModel->name;

        if ($podcastModel->parent !== null) {
            $record->parent_id = $podcastModel->parent->id;
        }

        $record->parent_chain = json_encode(array_map(
            static fn (PodcastCategoryModel $m) => $m->id,
            $podcastModel->parentChain,
        ));

        $this->saveNewRecord->save($record);

        foreach ($item['children'] as $child) {
            $this->processItem($child, $podcastModel);
        }
    }

    /**
     * @throws Throwable
     */
    private function populateRecordsByKeyPath(): void
    {
        /** @var PodcastCategoryRecord[] $allRecords */
        $allRecords = $this->recordQueryFactory
            ->make(new PodcastCategoryRecord())
            ->all();

        $allRecordsById = [];

        foreach ($allRecords as $record) {
            $allRecordsById[$record->id] = $record;
        }

        foreach ($allRecordsById as $id => $record) {
            $key = '';

            $chainIds = json_decode(
                $record->parent_chain,
                true
            );

            foreach ($chainIds as $chainId) {
                $key .= $allRecordsById[$chainId]->name . '/';
            }

            $key .= $record->name;

            $this->allRecordsByPathKey[$key] = $record;
        }
    }
}
