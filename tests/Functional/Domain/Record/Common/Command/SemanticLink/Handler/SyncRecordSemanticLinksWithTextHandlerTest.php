<?php

namespace Tests\Functional\Domain\Record\Common\Command\SemanticLink\Handler;

use App\Domain\Record\Common\Command\SemanticLink\SyncRecordSemanticLinksWithTextCommand;
use App\Domain\Record\Common\Entity\Record;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticlesForSemanticLinks;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithEqualsUrlRelativeArticle;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;

/**
 * @group semantic_link
 */
class SyncRecordSemanticLinksWithTextHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadArticlesForSemanticLinks::class,
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        $exceptedSemanticLink = $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);
        $record = $referenceRepository->getReference(LoadArticlesForSemanticLinks::REFERENCE_NAME);

        assert($record instanceof Record);

        $this->assertEmpty($record->getRecordSemanticLinks());

        $command = new SyncRecordSemanticLinksWithTextCommand($record->getId());

        $this->getCommandBus()->handle($command);

        $this->assertNotEmpty($record->getRecordSemanticLinks());
        $this->assertEquals([$exceptedSemanticLink], $record->getRecordSemanticLinks()->getSemanticLinks()->toArray());
    }

    public function testHandleWithEqualsUrlRelativeArticle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSemanticLinkWithEqualsUrlRelativeArticle::class,
        ])->getReferenceRepository();

        $record = $referenceRepository->getReference(LoadArticlesForSemanticLinks::REFERENCE_NAME);

        assert($record instanceof Record);

        $this->assertEmpty($record->getRecordSemanticLinks());

        $command = new SyncRecordSemanticLinksWithTextCommand($record->getId());

        $this->getCommandBus()->handle($command);

        $this->assertEmpty($record->getRecordSemanticLinks());
    }
}
