<?php

namespace Tests\Functional\Domain\Record\Common\Command\SemanticLink\Handler;

use App\Domain\Record\Common\Command\SemanticLink\DetachRecordSemanticLinksCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Entity\RecordSemanticLink;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleWithRecordSemanticLink;
use Tests\Functional\TestCase;

/**
 * @group record
 * @group semantic_link
 */
class DetachRecordSemanticLinksHandlerTest extends TestCase
{
    public function testRecordMustLoseSemanticLinksAfterHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadArticleWithRecordSemanticLink::class,
        ])->getReferenceRepository();

        $record = $referenceRepository->getReference(LoadArticleWithRecordSemanticLink::REFERENCE_NAME);
        assert($record instanceof Record);

        $recordSemanticLink = $record->getRecordSemanticLinks()->first();
        assert($recordSemanticLink instanceof RecordSemanticLink);

        $semanticLink = $recordSemanticLink->getSemanticLink();
        $expectedNumberOfLinkedRecord = $semanticLink->getNumberActiveLinks() - 1;

        $command = new DetachRecordSemanticLinksCommand($record);
        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $record->getRecordSemanticLinks());
        $this->assertEquals($expectedNumberOfLinkedRecord, $semanticLink->getNumberActiveLinks());
    }
}
