<?php

namespace Tests\Functional\Domain\SemanticLink\Command\Handler;

use App\Domain\SemanticLink\Command\CreateSemanticLinkCommand;
use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Domain\SemanticLink\Repository\SemanticLinkRepository;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;

/**
 * @group semantic_link
 */
class CreateSemanticLinkHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var SemanticLinkRepository $semanticLinkRepository */
        $semanticLinkRepository = $this->getContainer()->get(SemanticLinkRepository::class);

        $referenceRepository = $this->loadFixtures([
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        /** @var SemanticLink $expectedSemanticLink */
        $expectedSemanticLink = $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);

        $command = new CreateSemanticLinkCommand(Uuid::uuid4());
        $command->text = $expectedSemanticLink->getText().' orlando';
        $command->uri = $expectedSemanticLink->getUri();

        $this->getCommandBus()->handle($command);

        $semanticLink = $semanticLinkRepository->findOneByText($command->text);

        $this->assertEquals($command->id, $semanticLink->getId());
        $this->assertEquals($command->text, $semanticLink->getText());
        $this->assertEquals($command->uri, $semanticLink->getUri());
        $this->assertEquals(0, $semanticLink->getNumberActiveLinks());
    }
}
