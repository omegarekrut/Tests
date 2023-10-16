<?php

namespace Tests\Functional\Domain\User\Command\Subscription\Handler;

use App\Domain\User\Command\Subscription\SetEventsEmailFrequencyByHashCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\EmailFrequency;
use App\Domain\User\Generator\SubscribeNewsletterHashGenerator;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class SetEventsEmailFrequencyByHashHandlerTest extends TestCase
{
    /** @var SubscribeNewsletterHashGenerator */
    private $hashGenerator;
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->hashGenerator = $this->getContainer()->get(SubscribeNewsletterHashGenerator::class);
    }

    protected function tearDown(): void
    {
        unset($this->user);
        unset($this->hashGenerator);

        parent::tearDown();
    }

    public function testSetNewEventsEmailFrequencyValue(): void
    {
        $this->user->setEventsEmailFrequency(EmailFrequency::daily());

        $command = new SetEventsEmailFrequencyByHashCommand(
            $this->user,
            $this->hashGenerator->generate($this->user->getId())
        );
        $command->emailFrequencyValue = (string) EmailFrequency::never();

        $this->getCommandBus()->handle($command);

        $this->assertEquals($this->user->getEventsEmailFrequency(), EmailFrequency::never());
    }
}
