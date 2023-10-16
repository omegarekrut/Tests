<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Ban\Service\BanInterface;
use App\Domain\Log\Entity\SpammerDetectionLog;
use App\Domain\Log\Repository\SpammerDetectionLogRepository;
use App\Domain\SpamDetection\Event\SpammerDetectedEvent;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadSpammerUser;
use Tests\Functional\TestCase;

/**
 * @group spam-detection
 */
class SpamDetectionEventsSubscriberTest extends TestCase
{
    /** @var BanInterface */
    private $banStorage;
    /** @var SpammerDetectionLogRepository */
    private $spammerDetectionLogRepository;
    /** @var User */
    private $spammer;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadSpammerUser::class,
        ])->getReferenceRepository();

        $this->spammer = $referenceRepository->getReference(LoadSpammerUser::REFERENCE_NAME);
        $this->banStorage = $this->getContainer()->get(BanInterface::class);
        $this->spammerDetectionLogRepository = $this->getContainer()->get(SpammerDetectionLogRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->banStorage,
            $this->spammerDetectionLogRepository,
            $this->spammer
        );

        parent::tearDown();
    }

    public function testSpammerShouldBeBannedOnSpammerDetectedEvent(): void
    {
        $expectedDetectionReason = 'reason';
        $this->getEventDispatcher()->dispatch(new SpammerDetectedEvent($this->spammer, $expectedDetectionReason));

        $spammerBanInformation = $this->banStorage->getBanInformationByUserId($this->spammer->getId());

        $this->assertNotEmpty($spammerBanInformation);
        $this->assertStringContainsString($expectedDetectionReason, $spammerBanInformation->getCause());
        $this->assertTrue($this->banStorage->isBannedByIp($this->spammer->getLastVisit()->getLastVisitIp()));
    }

    public function testSpammerDetectionMustBeLoggedOnSpammerDetected(): void
    {
        $expectedDetectionReason = 'reason';
        $this->getEventDispatcher()->dispatch(new SpammerDetectedEvent($this->spammer, $expectedDetectionReason));

        $logEntries = $this->spammerDetectionLogRepository->findAllBySpammer($this->spammer);
        $this->assertCount(1, $logEntries);

        /** @var SpammerDetectionLog $logEntry */
        $logEntry = current($logEntries);
        $this->assertEquals($expectedDetectionReason, $logEntry->getDetectionReason());
    }
}
