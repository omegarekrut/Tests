<?php

namespace Tests\Functional\Domain\Record\News\Command\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\News\Command\CreateNewsCommand;
use App\Domain\Record\News\Repository\NewsRepository;
use App\Domain\Record\News\Entity\News;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

/**
 * @group news
 */
class CreateNewsCommandHandlerTest extends TestCase
{
    private User $user;
    private NewsRepository $newsRepository;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $this->newsRepository = $this->getEntityManager()->getRepository(News::class);
        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->newsRepository,
            $this->company
        );

        parent::tearDown();
    }

    public function testNewsIsCreatedAndSaved(): void
    {
        $command = $this->createNewsCommand($this->user);

        $this->getCommandBus()->handle($command);

        $newsList = $this->newsRepository->findAllByTitle($command->title);

        $this->assertCount(1, $newsList);

        $actualNews = current($newsList);
        assert($actualNews instanceof News);

        $this->assertEquals($command->preview, $actualNews->getPreview());
        $this->assertEquals($command->priority, $actualNews->getPriority());
        $this->assertEquals($command->text, $actualNews->getText());
        $this->assertNotEmpty($actualNews->getActualDateAt());
        $this->assertEquals($command->actual->format('Y-m-d H:i:s'), $actualNews->getActualDateAt()->format('Y-m-d H:i:s'));
        $this->assertEquals($command->publishAt->format('Y-m-d H:i:s'), $actualNews->getPublishAt()->format('Y-m-d H:i:s'));
        $this->assertEquals((string) $command->image, (string) $actualNews->getImage());
        $this->assertTrue($command->author === $actualNews->getAuthor());
    }

    public function testNewsIsCreatedByCompanyAuthorAndSaved(): void
    {
        $command = $this->createNewsCommand($this->company->getOwner());
        $command->companyAuthor = $this->company;

        $this->getCommandBus()->handle($command);

        $newsList = $this->newsRepository->findAllByTitle($command->title);

        $this->assertCount(1, $newsList);

        $actualNews = current($newsList);
        assert($actualNews instanceof News);

        $this->assertEquals($command->companyAuthor, $actualNews->getCompanyAuthor());
        $this->assertEquals($command->companyAuthor->getName(), $actualNews->getCompanyAuthorName());
    }

    private function createNewsCommand(AuthorInterface $author): CreateNewsCommand
    {
        $command = new CreateNewsCommand();
        $command->title = 'News title';
        $command->preview = 'preview';
        $command->priority = 3;
        $command->text = 'News text';
        $command->actual = Carbon::tomorrow();
        $command->publishAt = Carbon::now();
        $command->image = new Image('test.jpg');
        $command->author = $author;

        return $command;
    }
}
