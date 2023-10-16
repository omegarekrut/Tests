<?php

namespace Tests\Functional\Domain\Record\Tidings\Command\SemanticLink\Handler;

use App\Domain\Record\Tidings\Command\SemanticLink\SyncAllTidingsSemanticLinksWithTextCommand;
use App\Domain\Record\Tidings\Entity\Tidings;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsForSemanticLinks;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;

/**
 * @group semantic_link
 */
class SyncAllTidingsSemanticLinksWithTextHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTidingsForSemanticLinks::class,
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        $exceptedSemanticLink = $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);

        /** @var Tidings $tidings */
        $tidings = $referenceRepository->getReference(LoadTidingsForSemanticLinks::REFERENCE_NAME);
        $this->assertEmpty($tidings->getRecordSemanticLinks());

        $command = new SyncAllTidingsSemanticLinksWithTextCommand();

        $this->getCommandBus()->handle($command);

        $this->assertNotEmpty($tidings->getRecordSemanticLinks());
        $this->assertEquals([$exceptedSemanticLink], $tidings->getRecordSemanticLinks()->getSemanticLinks()->toArray());
    }
}
