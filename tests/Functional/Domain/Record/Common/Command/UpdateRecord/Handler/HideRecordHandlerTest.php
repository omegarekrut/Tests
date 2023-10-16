<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord\Handler;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Common\Command\UpdateRecord\HideRecordCommand;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\TestCase;

class HideRecordHandlerTest extends TestCase
{
    /** @var Article */
    private $record;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
        ])->getReferenceRepository();

        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
    }

    protected function tearDown(): void
    {
        unset($this->record);

        parent::tearDown();
    }

    public function testHideRecord(): void
    {
        $hideRecordCommand = new HideRecordCommand($this->record);
        $this->getCommandBus()->handle($hideRecordCommand);

        $this->assertTrue($this->record->isHidden());
    }

    public function testRecordCountInCategoryShouldDecreaseAfterHiding(): void
    {
        $sourceCategory = $this->record->getCategory();
        $expectedRecordCountInCategory = $sourceCategory->getRecordsCount() - 1;

        $hideRecordCommand = new HideRecordCommand($this->record);
        $this->getCommandBus()->handle($hideRecordCommand);

        $this->assertEquals($expectedRecordCountInCategory, $this->record->getCategory()->getRecordsCount());
    }
}
