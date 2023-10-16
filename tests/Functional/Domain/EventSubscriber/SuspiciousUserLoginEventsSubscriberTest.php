<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Log\Entity\SuspiciousLoginLog;
use App\Domain\Log\Event\SuspiciousUserLoginDetectedEvent;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithDotInUsername;
use Tests\Functional\TestCase;

/**
 * @group suspicious-user-login-events
 */
class SuspiciousUserLoginEventsSubscriberTest extends TestCase
{
    public function testSuspiciousLoginMustBeInLog(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadUserWithDotInUsername::class,
        ])->getReferenceRepository();

        /** @var User $testUser */
        $testUser = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        /** @var User $userWithDotInUsername */
        $userWithDotInUsername = $referenceRepository->getReference(LoadUserWithDotInUsername::REFERENCE_NAME);

        $this->getEventDispatcher()->dispatch(
            new SuspiciousUserLoginDetectedEvent($testUser, $userWithDotInUsername)
        );

        $suspiciousLoginLogRepository = $this->getEntityManager()->getRepository(SuspiciousLoginLog::class);

        $suspiciousLoginLog = $suspiciousLoginLogRepository->findOneBy(['newUser' => $userWithDotInUsername->getId()]);

        $this->assertNotNull($suspiciousLoginLog);
        $this->assertInstanceOf(SuspiciousLoginLog::class, $suspiciousLoginLog);
        $this->assertEquals($testUser, $suspiciousLoginLog->getOldUser());
        $this->assertEquals($userWithDotInUsername, $suspiciousLoginLog->getNewUser());
    }
}
