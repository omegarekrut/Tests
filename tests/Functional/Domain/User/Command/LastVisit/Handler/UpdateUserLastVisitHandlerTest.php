<?php

namespace Tests\Functional\Domain\User\Command\LastVisit\Handler;

use App\Domain\User\Command\LastVisit\UpdateUserLastVisitCommand;
use App\Domain\User\Entity\User;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class UpdateUserLastVisitHandlerTest extends TestCase
{
    public function testAfterHandleLastVisitDateAndIpUpdated(): void
    {
        $user = $this
            ->loadFixtures([LoadTestUser::class])
            ->getReferenceRepository()
            ->getReference(LoadTestUser::USER_TEST);

        assert($user instanceof User);

        $expectedLastVisitedIp = '192.168.1.121';
        $expectedLastVisitedAt = Carbon::yesterday();

        $command = new UpdateUserLastVisitCommand($user->getId(), $expectedLastVisitedAt, $expectedLastVisitedIp);

        $this->getCommandBus()->handle($command);

        $this->assertEquals($expectedLastVisitedIp, $user->getLastVisit()->getLastVisitIp());
        $this->assertEquals($expectedLastVisitedAt, $user->getLastVisit()->getLastVisitAt());
    }
}
