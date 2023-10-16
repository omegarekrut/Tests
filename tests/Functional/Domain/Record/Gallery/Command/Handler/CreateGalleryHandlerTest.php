<?php

namespace Tests\Functional\Domain\Record\Gallery\Command\Handler;

use App\Domain\Category\Collection\CategoryCollection;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Gallery\Command\CreateGalleryCommand;
use App\Domain\Record\Gallery\Command\Handler\CreateGalleryHandler;
use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\Record\Gallery\Event\GalleryCreatedEvent;
use App\Domain\Record\Gallery\Repository\GalleryRepository;
use App\Domain\Region\Entity\Region;
use App\Domain\Region\Repository\RegionRepository;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\EventDispatcherMock;

/**
 * @group gallery
 */
class CreateGalleryHandlerTest extends TestCase
{
    private User $user;
    private GalleryRepository $galleryRepository;
    private CategoryRepository $categoryRepository;
    private Region $region;
    private Company $company;
    private EventDispatcherMock $eventDisptacher;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCategories::class,
            LoadTestRegion::class,
            LoadUserWithAvatar::class,
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $this->galleryRepository = $this->getEntityManager()->getRepository(Gallery::class);
        $this->categoryRepository = $this->getContainer()->get(CategoryRepository::class);
        $this->region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);

        $this->eventDisptacher = new EventDispatcherMock();

        $this->handler = new CreateGalleryHandler(
            $this->getCommandBus(),
            $this->eventDisptacher,
            $this->galleryRepository,
            $this->getContainer()->get(RegionRepository::class)
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->galleryRepository,
            $this->categoryRepository,
            $this->company,
            $this->eventDisptacher
        );

        parent::tearDown();
    }

    public function testGalleryIsSaved(): void
    {
        $command = $this->getCreateGalleryCommand('_1');

        $this->handler->handle($command);

        $this->assertGalleryData($command);
    }

    public function testGalleryHandleGalleryCreatedEvent(): void
    {
        $command = $this->getCreateGalleryCommand('_1');

        $this->handler->handle($command);

        $dispatchedEvents = $this->eventDisptacher->getDispatchedEvents();

        $this->assertCount(1, $dispatchedEvents);
        $this->assertArrayHasKey(GalleryCreatedEvent::class, $dispatchedEvents);
    }

    public function testGalleryIsCreatedByCompanyAuthor(): void
    {
        $command = $this->getCreateGalleryCommand('_1');
        $command->author = $this->company->getOwner();
        $command->companyAuthor = $this->company;

        $this->getCommandBus()->handle($command);

        $actualGallery = $this->galleryRepository->findLastGalleryForUser($command->author);
        assert($actualGallery instanceof Gallery);

        $this->assertEquals($command->companyAuthor, $actualGallery->getCompanyAuthor());
        $this->assertEquals($command->companyAuthor->getName(), $actualGallery->getCompanyAuthorName());
    }

    /**
     * @param mixed[]
     */
    private function assertGalleryData(CreateGalleryCommand $createGalleryCommand): void
    {
        $galleryList = $this->galleryRepository->findAllByTitle($createGalleryCommand->title);

        $this->assertCount(1, $galleryList);

        $actualGallery = current($galleryList);
        assert($actualGallery instanceof Gallery);

        $this->assertEquals($createGalleryCommand->title, $actualGallery->getTitle());
        $this->assertEquals($createGalleryCommand->category, $actualGallery->getCategory());
        $this->assertEquals($createGalleryCommand->data, $actualGallery->getText());
        $this->assertEquals($createGalleryCommand->imageName, (string) $actualGallery->getImage());
        $this->assertEquals($createGalleryCommand->author, $actualGallery->getAuthor());
        $this->assertEquals((string) $createGalleryCommand->regionId, (string) $actualGallery->getRegion()->getId());
    }

    private function getCreateGalleryCommand(string $uniqKey): CreateGalleryCommand
    {
        $command = new CreateGalleryCommand($this->user);

        $command->category = $this->findAllNestedGalleryCategories()->first();
        $command->title = 'title'.$uniqKey;
        $command->data = 'data'.$uniqKey;
        $command->imageName = 'imageName.jpeg';
        $command->imageRotationAngle = 0;
        $command->regionId = (string) $this->region->getId();

        return $command;
    }

    private function findAllNestedGalleryCategories(): CategoryCollection
    {
        $rootCategory = $this->categoryRepository->findRootCategoryBySlug(Category::ROOT_GALLERY_SLUG);

        return $rootCategory->getChildren();
    }
}
