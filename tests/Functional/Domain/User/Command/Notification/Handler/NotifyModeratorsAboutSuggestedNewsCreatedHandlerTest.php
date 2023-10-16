<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\SuggestedNews\Entity\SuggestedNews;
use App\Domain\User\Command\Notification\NotifyModeratorsAboutSuggestedNewsCreatedCommand;
use App\Domain\User\Entity\Notification\SuggestedNewsCreatedNotification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\SuggestedNews\LoadSuggestedNewsByUserFixture;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\Functional\TestCase;

class NotifyModeratorsAboutSuggestedNewsCreatedHandlerTest extends TestCase
{
    private SuggestedNews $suggestedNews;
    private User $moderator;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadSuggestedNewsByUserFixture::class,
            LoadModeratorUser::class,
        ])->getReferenceRepository();

        $this->suggestedNews = $referenceRepository->getReference(LoadSuggestedNewsByUserFixture::REFERENCE_NAME);
        $this->moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->suggestedNews);

        parent::tearDown();
    }

    public function testUserMustReceiveNotificationAfterHandle(): void
    {
        $command = new NotifyModeratorsAboutSuggestedNewsCreatedCommand($this->suggestedNews);

        $this->getCommandBus()->handle($command);

        $actualNotification = $this->moderator->getUnreadNotifications()->first();

        $this->assertInstanceOf(SuggestedNewsCreatedNotification::class, $actualNotification);
        assert($actualNotification instanceof SuggestedNewsCreatedNotification);

        $this->assertTrue($this->suggestedNews === $actualNotification->getSuggestedNews());
    }
}
