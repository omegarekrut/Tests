<?php

namespace Tests\Functional\Domain\SuggestedNews\Command\Handler;

use App\Domain\SuggestedNews\Command\CreateSuggestedNewsCommand;
use App\Domain\SuggestedNews\Repository\SuggestedNewsRepository;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use App\Domain\User\Entity\User;
use Tests\Functional\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group suggested-news
 */
class CreateSuggestedNewsHandlerTest extends TestCase
{
    private User $author;
    private SuggestedNewsRepository $suggestedNewsRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->author = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->suggestedNewsRepository = $this->getContainer()->get(SuggestedNewsRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->author,
            $this->suggestedNewsRepository
        );

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $createSuggestedNewsCommand = new CreateSuggestedNewsCommand(Uuid::uuid4(), $this->author);
        $createSuggestedNewsCommand->title = 'Тестовый заголовок';
        $createSuggestedNewsCommand->fullText = 'Тестовый текст';

        $this->getCommandBus()->handle($createSuggestedNewsCommand);

        $suggestedNews = $this->suggestedNewsRepository->findById($createSuggestedNewsCommand->getId());

        $this->assertEquals($createSuggestedNewsCommand->getId(), $suggestedNews->getId());
        $this->assertEquals($createSuggestedNewsCommand->title, $suggestedNews->getTitle());
        $this->assertEquals($createSuggestedNewsCommand->fullText, $suggestedNews->getFullText());
        $this->assertEquals($createSuggestedNewsCommand->getAuthor(), $suggestedNews->getAuthor());
    }
}
