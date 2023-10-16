<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord\Handler;

use App\Doctrine\NoMagic\IgnoreDoctrineFiltersAndSoftDeletableEnvironment;
use App\Domain\Record\Common\Command\UpdateRecord\RestoreRecordCommand;
use App\Domain\Record\Common\Entity\Record;
use Tests\DataFixtures\ORM\Record\LoadHiddenVideo;
use Tests\Functional\TestCase;

class RestoreRecordHandlerTest extends TestCase
{
    /** @var Record */
    private $hiddenRecord;
    /** @var IgnoreDoctrineFiltersAndSoftDeletableEnvironment */
    private $ignoreDoctrineFiltersAndSoftDeletableEnvironment;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadHiddenVideo::class,
        ])->getReferenceRepository();

        $this->hiddenRecord = $referenceRepository->getReference(LoadHiddenVideo::REFERENCE_NAME);
        $this->ignoreDoctrineFiltersAndSoftDeletableEnvironment = $this->getContainer()->get(IgnoreDoctrineFiltersAndSoftDeletableEnvironment::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->hiddenRecord,
            $this->ignoreDoctrineFiltersAndSoftDeletableEnvironment
        );

        parent::tearDown();
    }

    public function testRestoreRecord(): void
    {
        ($this->ignoreDoctrineFiltersAndSoftDeletableEnvironment)(function () {
            $restoreRecordCommand = new RestoreRecordCommand($this->hiddenRecord);
            $this->getCommandBus()->handle($restoreRecordCommand);

            $this->assertFalse($this->hiddenRecord->isHidden());
        });
    }

    public function testRecordCountInCategoryShouldIncreaseAfterRestore(): void
    {
        ($this->ignoreDoctrineFiltersAndSoftDeletableEnvironment)(function () {
            $sourceCategory = $this->hiddenRecord->getCategory();
            $expectedRecordCountInCategory = $sourceCategory->getRecordsCount() + 1;

            $restoreRecordCommand = new RestoreRecordCommand($this->hiddenRecord);
            $this->getCommandBus()->handle($restoreRecordCommand);

            $this->assertEquals($expectedRecordCountInCategory, $this->hiddenRecord->getCategory()->getRecordsCount());
        });
    }
}
