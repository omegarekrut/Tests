<?php

namespace Tests\Functional\Domain\Record\Video\Command\Handler;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Video\Command\UpdateVideoInAdminCommand;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\Region\Entity\Region;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\Functional\TestCase;

/**
 * @group video
 */
class UpdateVideoInAdminHandlerTest extends TestCase
{
    public function testVideoIsChanged(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadVideos::class,
            LoadCategories::class,
            LoadTestRegion::class,
        ])->getReferenceRepository();

        $video = $referenceRepository->getReference(LoadVideos::getRandReferenceName());
        assert($video instanceof Video);

        $videoCategory = $referenceRepository->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_VIDEO));
        assert($videoCategory instanceof Category);

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $command = new UpdateVideoInAdminCommand($video);
        $command->category = $videoCategory;
        $command->videoUrl = 'new videoUrl';
        $command->title = 'new title';
        $command->description = 'new description';
        $command->iframe = 'new iframe';
        $command->image = new Image('new-file.jpg');
        $command->region = $region;

        $now = Carbon::create();
        Carbon::setTestNow($now);

        try {
            $this->getCommandBus()->handle($command);

            $this->assertEquals($command->category, $video->getCategory());
            $this->assertEquals($command->videoUrl, $video->getVideoUrl());
            $this->assertEquals($command->title, $video->getTitle());
            $this->assertEquals($command->description, $video->getDescription());
            $this->assertEquals($command->iframe, $video->getIframe());
            $this->assertEquals($command->region, $video->getRegion());
            $this->assertEquals((string) $command->image, (string) $video->getImage());
            $this->assertEquals($now, $video->getUpdatedAt());
        } finally {
            Carbon::setTestNow();
        }
    }
}
