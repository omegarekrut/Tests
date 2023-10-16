<?php

namespace Tests\Functional\Domain\Record\Gallery\Command\Handler;

use App\Domain\Record\Gallery\Command\UpdateGalleryCommand;
use App\Domain\Record\Gallery\Entity\Gallery;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;
use Tests\Functional\TestCase;

/**
 * @group gallery
 */
class UpdateGalleryHandlerTest extends TestCase
{
    public function testGalleryIsChanged(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGallery::class,
            LoadCategories::class,
            LoadNovosibirskRegion::class,
        ])->getReferenceRepository();

        /** @var Gallery $gallery */
        $gallery = $referenceRepository->getReference(LoadGallery::getRandReferenceName());
        $category = $referenceRepository->getReference(LoadCategories::getReferenceRootName(LoadCategories::ROOT_VIDEO));
        $region = $referenceRepository->getReference(LoadNovosibirskRegion::REFERENCE_NAME);

        $command = new UpdateGalleryCommand($gallery);
        $command->category = $category;
        $command->title = sprintf('%s New', $command->title);
        $command->data = sprintf('%s New', $command->data);
        $command->imageRotationAngle = 90;
        $command->regionId = (string) $region->getId();

        $now = Carbon::create();
        Carbon::setTestNow($now);

        try {
            $this->getCommandBus()->handle($command);

            $this->assertEquals($category, $gallery->getCategory());
            $this->assertEquals($command->title, $gallery->getTitle());
            $this->assertEquals($command->data, $gallery->getDescription());
            $this->assertEquals('transformer image name.jpg', (string) $gallery->getImage());
            $this->assertEquals($now, $gallery->getUpdatedAt());
            $this->assertEquals($command->regionId, (string) $gallery->getRegion()->getId());
        } finally {
            Carbon::setTestNow();
        }
    }
}
