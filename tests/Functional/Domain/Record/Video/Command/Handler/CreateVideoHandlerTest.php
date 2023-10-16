<?php

namespace Tests\Functional\Domain\Record\Video\Command\Handler;

use App\Domain\Category\Entity\Category;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Video\Command\CreateVideoCommand;
use App\Domain\Record\Video\Command\Handler\CreateVideoHandler;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\Region\Entity\Region;
use App\Domain\Record\Video\Repository\VideoRepository;
use App\Domain\Region\Repository\RegionRepository;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use App\Module\VideoInformationLoader\Exception\VideoInformationCouldNotBeLoaderException;
use App\Module\VideoInformationLoader\VideoInformation;
use App\Module\VideoInformationLoader\VideoInformationLoaderInterface;
use App\Util\ImageStorage\Image;
use League\Tactician\CommandBus;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group video
 */
class CreateVideoHandlerTest extends TestCase
{
    private Company $company;
    private User $user;
    private Category $videoCategory;
    private VideoRepository $videoRepository;
    private Region $region;
    private RegionRepository $regionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadCategories::class,
            LoadTestRegion::class,
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->videoRepository = $this->getEntityManager()->getRepository(Video::class);
        $this->regionRepository = $this->getContainer()->get(RegionRepository::class);
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->videoCategory = $referenceRepository->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_VIDEO));
        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $this->region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
    }

    /**
     * @throws VideoInformationCouldNotBeLoaderException
     */
    public function testVideoIsCreatedAndSaved(): void
    {
        $expectedUploadedImageName = 'uploaded-image.name';

        $commandBus = $this->createCommandBusForHandleUploadImageCommand($expectedUploadedImageName);
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');

        $commandHandler = new CreateVideoHandler($this->videoRepository, $this->createVideoInformationLoaderInterfaceMock(), $commandBus, $eventDispatcher, $this->regionRepository);

        $command = $this->createVideoCommand($this->user);

        $commandHandler->handle($command);

        $videoList = $this->videoRepository->findAllByTitle($command->title);

        $this->assertCount(1, $videoList);

        $actualVideo = current($videoList);
        assert($actualVideo instanceof Video);

        $this->assertEquals($command->category, $actualVideo->getCategory());
        $this->assertEquals($command->regionId, $actualVideo->getRegion()->getId());
        $this->assertEquals($command->videoUrl, $actualVideo->getVideoUrl());
        $this->assertEquals($command->title, $actualVideo->getTitle());
        $this->assertEquals($command->description, $actualVideo->getDescription());
        $this->assertEquals($expectedUploadedImageName, $actualVideo->getImage()->getFilename());
        $this->assertEquals($this->createVideoInformation()->getHtmlCode(), $actualVideo->getIframe());
    }

    /**
     * @throws VideoInformationCouldNotBeLoaderException
     */
    public function testVideoIsCreatedByCompanyAuthorAndSaved(): void
    {
        $uploadedImageName = 'uploaded-image.name';

        $commandBus = $this->createCommandBusForHandleUploadImageCommand($uploadedImageName);
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $commandHandler = new CreateVideoHandler($this->videoRepository, $this->createVideoInformationLoaderInterfaceMock(), $commandBus, $eventDispatcher, $this->regionRepository);

        $command = $this->createVideoCommand($this->company->getOwner());
        $command->companyAuthor = $this->company;

        $commandHandler->handle($command);

        $videoList = $this->videoRepository->findAllByTitle($command->title);

        $this->assertCount(1, $videoList);

        $actualVideo = current($videoList);
        assert($actualVideo instanceof Video);

        $this->assertEquals($command->companyAuthor, $actualVideo->getCompanyAuthor());
        $this->assertEquals($command->companyAuthor->getName(), $actualVideo->getCompanyAuthorName());
    }

    private function createVideoInformationLoaderInterfaceMock(): VideoInformationLoaderInterface
    {
        $videoFactoryMock = $this->createMock(VideoInformationLoaderInterface::class);
        $videoFactoryMock
            ->method('loadInformation')
            ->willReturn($this->createVideoInformation());

        return $videoFactoryMock;
    }

    public function createVideoInformation(): VideoInformation
    {
        return new VideoInformation(
            'url',
            'title',
            'imageUrl',
            '<iframe></iframe>'
        );
    }

    private function createCommandBusForHandleUploadImageCommand(string $uploadedImageName): CommandBus
    {
        $commandBusMock = $this->createMock(CommandBus::class);
        $commandBusMock
            ->method('handle')
            ->willReturn(new Image($uploadedImageName));

        return $commandBusMock;
    }

    private function createVideoCommand(AuthorInterface $author): CreateVideoCommand
    {
        $command = new CreateVideoCommand($author);
        $command->category = $this->videoCategory;
        $command->videoUrl = 'http://user.video.url.com';
        $command->title = 'new video title ';
        $command->description = 'new video description';
        $command->regionId = $this->region->getId();

        return $command;
    }
}
