<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\SuggestedNews\Entity\SuggestedNews;
use App\Domain\SuggestedNews\Event\SuggestedNewsCreatedEvent;
use App\Domain\User\Entity\Notification\SuggestedNewsCreatedNotification;
use Tests\DataFixtures\ORM\SuggestedNews\LoadSuggestedNewsByUserFixture;
use Tests\Functional\TestCase;

class SuggestedNewsEventsSubscriberTest extends TestCase
{
    private SuggestedNews $suggestedNews;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadSuggestedNewsByUserFixture::class,
        ])->getReferenceRepository();

        $this->suggestedNews = $referenceRepository->getReference(LoadSuggestedNewsByUserFixture::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->suggestedNews);

        parent::tearDown();
    }

    public function testNotificationShouldBeSentAfterSuggestedNewsCreation(): void
    {
        $suggestedNewsNotificationsRepository = $this->getEntityManager()->getRepository(SuggestedNewsCreatedNotification::class);

        $numberOfNotifications = $suggestedNewsNotificationsRepository->count([]);

        $this->getEventDispatcher()->dispatch(new SuggestedNewsCreatedEvent($this->suggestedNews));

        $this->assertGreaterThan($numberOfNotifications, $suggestedNewsNotificationsRepository->count([]));
    }
}
