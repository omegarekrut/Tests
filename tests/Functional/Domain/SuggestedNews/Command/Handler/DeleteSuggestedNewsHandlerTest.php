<?php

namespace Tests\Functional\Domain\SuggestedNews\Command\Handler;

use App\Domain\SuggestedNews\Command\DeleteSuggestedNewsCommand;
use App\Domain\SuggestedNews\Entity\SuggestedNews;
use App\Domain\SuggestedNews\Repository\SuggestedNewsRepository;
use Tests\DataFixtures\ORM\SuggestedNews\LoadSuggestedNewsByUserFixture;
use Tests\Functional\TestCase;

/**
 * @group suggested-news
 */
class DeleteSuggestedNewsHandlerTest extends TestCase
{
    private SuggestedNews $suggestedNews;
    private SuggestedNewsRepository $suggestedNewsRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadSuggestedNewsByUserFixture::class,
        ])->getReferenceRepository();

        $this->suggestedNews = $referenceRepository->getReference(LoadSuggestedNewsByUserFixture::REFERENCE_NAME);
        $this->suggestedNewsRepository = $this->getContainer()->get(SuggestedNewsRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->suggestedNews,
            $this->suggestedNewsRepository
        );

        parent::tearDown();
    }

    public function testAfterHandlerNewsMustBeDeleted(): void
    {
        $command = new DeleteSuggestedNewsCommand($this->suggestedNews);

        $this->getCommandBus()->handle($command);

        $actualSuggestedNews = $this->suggestedNewsRepository->findById($this->suggestedNews->getId());

        $this->assertNull($actualSuggestedNews);
    }
}
