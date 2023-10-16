<?php

namespace Tests\Functional\Domain\Record\Common\Command\UpdateRecord\Handler;

use App\Domain\Record\Common\Command\UpdateRecord\OnlyReadRecordCommentsCommand;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoWithDisallowedCommentsAccess;
use Tests\Functional\TestCase;

/**
 * @group record
 */
class OnlyReadRecordCommentsHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadVideoWithDisallowedCommentsAccess::class,
        ])->getReferenceRepository();

        $video = $referenceRepository->getReference(LoadVideoWithDisallowedCommentsAccess::REFERENCE_NAME);

        $onlyReadRecordCommentsCommand = new OnlyReadRecordCommentsCommand($video);
        $this->getCommandBus()->handle($onlyReadRecordCommentsCommand);

        $this->assertTrue($video->areCommentsOnlyRead());
    }
}
