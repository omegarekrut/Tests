<?php

namespace Tests\Functional\Domain\Record\Video\Command\Handler;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Video\Command\UpdateVideoCommand;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\Region\Entity\Region;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\Functional\TestCase;

/**
 * @group video
 */
class UpdateVideoHandlerTest extends TestCase
{
    public function testVideoIsChanged(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadVideos::class,
            LoadCategories::class,
            LoadTestRegion::class,
        ])->getReferenceRepository();

        $category = $referenceRepository->getReference(LoadCategories::getReferenceRootName(LoadCategories::ROOT_VIDEO));
        assert($category instanceof Category);

        $video = $referenceRepository->getReference(LoadVideos::getRandReferenceName());
        assert($video instanceof Video);

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $command = new UpdateVideoCommand($video);
        $command->category = $category;
        $command->title = 'title by user';
        $command->description = 'description by user';
        $command->regionId = $region->getId();

        $now = Carbon::create();
        Carbon::setTestNow($now);

        try {
            $this->getCommandBus()->handle($command);

            $this->assertNotNull($video);
            $this->assertEquals($category, $video->getCategory());
            $this->assertEquals($command->title, $video->getTitle());
            $this->assertEquals($command->description, $video->getDescription());
            $this->assertEquals($command->regionId, $video->getRegion()->getId());
            $this->assertEquals($now, $video->getUpdatedAt());
        } finally {
            Carbon::setTestNow();
        }
    }
}
