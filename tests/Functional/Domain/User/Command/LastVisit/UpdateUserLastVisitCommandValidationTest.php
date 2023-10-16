<?php

namespace Tests\Functional\Domain\User\Command\LastVisit;

use App\Domain\User\Command\LastVisit\UpdateUserLastVisitCommand;
use App\Domain\User\Entity\User;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

class UpdateUserLastVisitCommandValidationTest extends ValidationTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this
            ->loadFixtures([LoadTestUser::class])
            ->getReferenceRepository()
            ->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset($this->user);

        parent::tearDown();
    }

    public function testValidCommandShouldNotFailValidation(): void
    {
        $command = new UpdateUserLastVisitCommand($this->user->getId(), Carbon::now(), '127.0.0.1');

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testInvalidUserIdFieldShouldFailValidation(): void
    {
        $command = new UpdateUserLastVisitCommand(1_000_000, Carbon::now(), '127.0.0.1');

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('userId', 'User not found.');
    }

    public function testInvalidVisitedAtFieldShouldFailValidation(): void
    {
        $command = new UpdateUserLastVisitCommand($this->user->getId(), Carbon::tomorrow(), '127.0.0.1');

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('visitedAt', 'Visiting date must be in the past.');
    }

    public function testInvalidIpAddressFieldShouldFailValidation(): void
    {
        $command = new UpdateUserLastVisitCommand($this->user->getId(), Carbon::now(), '999.999.999.999');

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('ipAddress', 'Invalid IP address.');
    }
}
