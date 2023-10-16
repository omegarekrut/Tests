<?php

namespace Tests\Functional\Domain\Log\Command\Handler;

use App\Domain\Log\Command\LogSuspiciousLoginCommand;
use App\Domain\Log\Entity\SuspiciousLoginLog;
use App\Domain\Log\Repository\SuspiciousLoginLogRepository;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithDotInUsername;
use Tests\Functional\TestCase;

class LogSuspiciousLoginHandlerTest extends TestCase
{
    public function testAfterHandlingUsersMustBeInLog(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadUserWithDotInUsername::class,
        ])->getReferenceRepository();

        /** @var User $previousAuthorizedUser */
        $previousAuthorizedUser = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        /** @var User $currentAuthorizedUser */
        $currentAuthorizedUser = $referenceRepository->getReference(LoadUserWithDotInUsername::REFERENCE_NAME);

        $logSuspiciousLoginCommand = new LogSuspiciousLoginCommand();
        $logSuspiciousLoginCommand->currentAuthorizedUser = $currentAuthorizedUser;
        $logSuspiciousLoginCommand->previousAuthorizedUser = $previousAuthorizedUser;

        $this->getCommandBus()->handle($logSuspiciousLoginCommand);

        /** @var SuspiciousLoginLogRepository $suspiciousLoginLogRepository */
        $suspiciousLoginLogRepository = $this->getEntityManager()->getRepository(SuspiciousLoginLog::class);

        /** @var SuspiciousLoginLog $suspiciousLoginLog */
        $suspiciousLoginLog = $suspiciousLoginLogRepository->findLatestByNewUser($currentAuthorizedUser);

        $this->assertInstanceOf(SuspiciousLoginLog::class, $suspiciousLoginLog);
        $this->assertEquals($currentAuthorizedUser->getId(), $suspiciousLoginLog->getNewUser()->getId());
        $this->assertEquals($previousAuthorizedUser->getId(), $suspiciousLoginLog->getOldUser()->getId());
    }
}
