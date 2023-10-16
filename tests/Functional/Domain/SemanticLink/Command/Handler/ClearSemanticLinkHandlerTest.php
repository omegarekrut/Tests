<?php

namespace Tests\Functional\Domain\SemanticLink\Command\Handler;

use App\Domain\SemanticLink\Command\ClearSemanticLinksCommand;
use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Domain\SemanticLink\Repository\SemanticLinkRepository;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;

/**
 * @group semantic_link
 */
class ClearSemanticLinkHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var SemanticLinkRepository $semanticLinkRepository */
        $semanticLinkRepository = $this->getContainer()->get(SemanticLinkRepository::class);

        $referenceRepository = $this->loadFixtures([
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        /** @var SemanticLink $expectedSemanticLink */
        $expectedSemanticLink = clone $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);

        $command = new ClearSemanticLinksCommand();
        $this->getCommandBus()->handle($command);

        $this->getEntityManager()->clear();

        $semanticLink = $semanticLinkRepository->findOneByText($expectedSemanticLink->getText());

        $this->assertEmpty($semanticLink);
    }
}
