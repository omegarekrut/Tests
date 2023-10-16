<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Record\News\Entity\News;
use App\Domain\Record\News\Event\NewsCreatedEvent;
use App\Domain\Record\News\Event\NewsUpdatedEvent;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Record\LoadNewsWithHashtagInText;
use Tests\Functional\TestCase;

/**
 * @group news-events
 */
class NewsEventsSubscriberTest extends TestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([LoadNewsWithHashtagInText::class])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset($this->referenceRepository);

        parent::tearDown();
    }

    public function testWhenCreatingNewsHashtagsMustBeUpdated(): void
    {
        /** @var News $news */
        $news = $this->referenceRepository->getReference(LoadNewsWithHashtagInText::REFERENCE_NAME);

        $this->getEventDispatcher()->dispatch(new NewsCreatedEvent($news));

        $this->assertCount(1, $news->getHashtags());
    }

    public function testWhenUpdatingNewsHashtagsMustBeUpdated(): void
    {
        /** @var News $news */
        $news = $this->referenceRepository->getReference(LoadNewsWithHashtagInText::REFERENCE_NAME);

        $this->getEventDispatcher()->dispatch(new NewsUpdatedEvent($news));

        $this->assertCount(1, $news->getHashtags());
    }
}
