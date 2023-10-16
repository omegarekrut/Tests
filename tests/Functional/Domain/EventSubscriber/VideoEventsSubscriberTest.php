<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Record\Video\Entity\Video;
use App\Domain\Record\Video\Event\VideoUpdatedEvent;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoWithHashtagInText;
use Tests\Functional\TestCase;

class VideoEventsSubscriberTest extends TestCase
{
    /** @var ReferenceRepository  */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([LoadVideoWithHashtagInText::class])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset($this->referenceRepository);

        parent::tearDown();
    }

    public function testWhenUpdatingVideoHashtagsMustBeUpdated(): void
    {
        /** @var Video $video */
        $video = $this->referenceRepository->getReference(LoadVideoWithHashtagInText::REFERENCE_NAME);

        $this->getEventDispatcher()->dispatch(new VideoUpdatedEvent($video));

        $this->assertCount(1, $video->getHashtags());
    }
}
