<?php

namespace Tests\Functional\Domain\User\Command\Subscription\Handler;

use App\Domain\User\Command\Subscription\SetEventsEmailFrequencyCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\EmailFrequency;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class SetEventsEmailFrequencyHandlerTest extends TestCase
{
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
    }

    protected function tearDown(): void
    {
        unset($this->user);

        parent::tearDown();
    }

    public function testSetNewEventsEmailFrequencyValue(): void
    {
        $this->user->setEventsEmailFrequency(EmailFrequency::daily());

        $command = new SetEventsEmailFrequencyCommand($this->user);
        $command->emailFrequencyValue = (string) EmailFrequency::never();

        $this->getCommandBus()->handle($command);

        $this->assertEquals($this->user->getEventsEmailFrequency(), EmailFrequency::never());
    }
}
