<?php

namespace Tests\Functional\Domain\Log\Command\Handler;

use App\Domain\Log\Command\LogSpammerDetectionCommand;
use App\Domain\Log\Entity\SpammerDetectionLog;
use App\Domain\Log\Repository\SpammerDetectionLogRepository;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadSpammerUser;
use Tests\Functional\TestCase;

/**
 * @group log
 * @group spam-detection
 */
class LogSpammerDetectionHandlerTest extends TestCase
{
    /** @var User */
    private $user;
    /** @var SpammerDetectionLogRepository */
    private $spammerDetectionLogRepository;
    /** @var Record */
    private $record;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadSpammerUser::class,
            LoadArticles::class
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadSpammerUser::REFERENCE_NAME);
        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->spammerDetectionLogRepository = $this->getContainer()->get(SpammerDetectionLogRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->record,
            $this->spammerDetectionLogRepository
        );

        parent::tearDown();
    }

    public function testAfterHandlingDetectionMustBeLogged(): void
    {
        $command = new LogSpammerDetectionCommand($this->user);
        $command->detectionReason = 'reason';

        $this->getCommandBus()->handle($command);

        /** @var SpammerDetectionLog|null $actualLog */
        $logs = $this->spammerDetectionLogRepository->findAllBySpammer($this->user);
        $actualLog = current($logs);

        $this->assertNotEmpty($actualLog);
        $this->assertTrue($command->getSpammer() === $actualLog->getSpammer());
        $this->assertEquals($command->detectionReason, $actualLog->getdetectionReason());
    }

    public function testDetectionMustLoggedWithAllSpammerPromotedResources(): void
    {
        $expectedPromotedResources = ['http://spam.resource', 'http://other.spam/resource'];

        foreach ($expectedPromotedResources as $url) {
            $this->record->addComment(Uuid::uuid4(), $this->getFaker()->regexify('[A-Za-z0-9]{20}'), $url, $this->user);
        }

        $this->getEntityManager()->flush();

        $command = new LogSpammerDetectionCommand($this->user);
        $command->detectionReason = 'reason';

        $this->getCommandBus()->handle($command);

        $logs = $this->spammerDetectionLogRepository->findAllBySpammer($this->user);
        /** @var SpammerDetectionLog|null $log */
        $log = current($logs);

        $promotedResources = $log->getPromotedResources();
        $this->assertCount(2, $promotedResources);

        foreach ($expectedPromotedResources as $expectedDomain => $expectedUrl) {
            $this->assertNotEmpty($promotedResources->findByUrl($expectedUrl));
        }
    }
}
