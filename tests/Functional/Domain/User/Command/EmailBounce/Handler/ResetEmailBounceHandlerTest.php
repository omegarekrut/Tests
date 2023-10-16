<?php

namespace Tests\Functional\Domain\User\Command\EmailBounce\Handler;

use App\Domain\User\Command\EmailBounce\ResetEmailBounceCommand;
use App\Domain\User\Entity\User;
use App\Module\EmailSuppression\EmailSuppressionManagerInterface;
use App\Module\EmailSuppression\EmailSuppressionManagerMock;
use Tests\DataFixtures\ORM\User\LoadUserWithBouncedEmail;
use Tests\Functional\TestCase;

class ResetEmailBounceHandlerTest extends TestCase
{
    /** @var User */
    private $user;
    /** @var EmailSuppressionManagerMock */
    private $emailSuppressionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithBouncedEmail::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithBouncedEmail::NAME);
        $this->emailSuppressionManager = $this->getContainer()->get(EmailSuppressionManagerInterface::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->emailSuppressionManager
        );

        parent::tearDown();
    }

    public function testResetEmailBounce(): void
    {
        $command = new ResetEmailBounceCommand();
        $command->user = $this->user;

        $this->getCommandBus()->handle($command);

        $this->assertFalse($this->user->getEmail()->isBounced());
        $this->assertEquals($this->user->getEmailAddress(), $this->emailSuppressionManager->getResetSuppressionEmail());
    }
}
