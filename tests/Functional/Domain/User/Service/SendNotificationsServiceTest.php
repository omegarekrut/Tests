<?php

namespace Tests\Functional\Domain\User\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\EmailFrequency;
use App\Domain\User\Service\SendNotificationsService;
use Tests\DataFixtures\ORM\User\LoadUserWithUnreadNotifications;
use Tests\Functional\TestCase;

/**
 * @group user
 */
class SendNotificationsServiceTest extends TestCase
{
    /** @var SendNotificationsService */
    private $sendNotificationsService;
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithUnreadNotifications::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithUnreadNotifications::REFERENCE_NAME);
        $this->sendNotificationsService = $this->getContainer()->get(SendNotificationsService::class);
    }

    protected function tearDown(): void
    {
        unset($this->user, $this->sendNotificationsService);

        parent::tearDown();
    }

    public function testSendNotificationMail(): void
    {
        $expectedRecipient = sprintf('To: %s', $this->user->getEmail());

        $this->sendNotificationsService->run(EmailFrequency::daily(), 1);

        $firstSentMail = $this->loadLastEmailMessage();

        $this->assertStringContainsString($expectedRecipient, $firstSentMail);

        $this->sendNotificationsService->run(EmailFrequency::daily(), 1);

        $secondSentMail = $this->loadLastEmailMessage();

        $this->assertEquals($firstSentMail, $secondSentMail);
    }
}
