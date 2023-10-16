<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord\Handler;

use App\Domain\Record\Common\Command\UpdateRecord\DisallowRecordCommentsCommand;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoWithoutImage;
use Tests\Functional\TestCase;

/**
 * @group record
 */
class DisallowRecordCommentsHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadVideoWithoutImage::class,
        ])->getReferenceRepository();

        $video = $referenceRepository->getReference(LoadVideoWithoutImage::REFERENCE_NAME);

        $disallowRecordCommentsCommand = new DisallowRecordCommentsCommand($video);
        $this->getCommandBus()->handle($disallowRecordCommentsCommand);

        $this->assertTrue($video->areCommentsDisallowed());
    }
}
