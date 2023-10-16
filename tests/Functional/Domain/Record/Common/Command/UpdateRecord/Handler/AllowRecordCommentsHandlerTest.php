<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord\Handler;

use App\Domain\Record\Common\Command\UpdateRecord\AllowRecordCommentsCommand;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoWithDisallowedCommentsAccess;
use Tests\Functional\TestCase;

/**
 * @group record
 */
class AllowRecordCommentsHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadVideoWithDisallowedCommentsAccess::class,
        ])->getReferenceRepository();

        $video = $referenceRepository->getReference(LoadVideoWithDisallowedCommentsAccess::REFERENCE_NAME);

        $allowRecordCommentsCommand = new AllowRecordCommentsCommand($video);
        $this->getCommandBus()->handle($allowRecordCommentsCommand);

        $this->assertTrue($video->areCommentsAllowed());
    }
}
