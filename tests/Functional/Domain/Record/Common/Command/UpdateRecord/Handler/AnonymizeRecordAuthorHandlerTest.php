<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord\Handler;

use App\Domain\Record\Common\Command\UpdateRecord\AnonymizeRecordAuthorCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Module\Author\AnonymousAuthor;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\TestCase;

/**
 * @group record
 */
class AnonymizeRecordAuthorHandlerTest extends TestCase
{
    /** @var Record */
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

    public function testAfterHandlingRecordAuthorShouldBeAnonymous(): void
    {
        $sourceRecordAuthor = clone $this->record->getAuthor();
        $this->assertNotInstanceOf(AnonymousAuthor::class, $sourceRecordAuthor);

        $command = new AnonymizeRecordAuthorCommand($this->record);
        $this->getCommandBus()->handle($command);

        $this->assertInstanceOf(AnonymousAuthor::class, $this->record->getAuthor());
        $this->assertEquals($sourceRecordAuthor->getUsername(), $this->record->getAuthor()->getUsername());
    }
}
