<?php

namespace Tests\Functional\Domain\Mailing;

use App\Domain\Mailing\ResetUserSuppressionMailer;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadUserWithBouncedEmail;
use Tests\Functional\TestCase;

/**
 * @group mailing
 * @group mailer
 */
class ResetUserSuppressionMailerTest extends TestCase
{
    /** @var User */
    private $bouncedUser;
    /** @var ResetUserSuppressionMailer */
    private $resetUserSuppressionMailer;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithBouncedEmail::class,
        ])->getReferenceRepository();

        $this->bouncedUser = $referenceRepository->getReference(LoadUserWithBouncedEmail::NAME);
        $this->resetUserSuppressionMailer = $this->getContainer()->get(ResetUserSuppressionMailer::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset(
            $this->bouncedUser,
            $this->resetUserSuppressionMailer
        );
    }

    public function testAfterSendEmailUserWithEmailShouldBeUnsuppressed(): void
    {
        $this->assertTrue($this->bouncedUser->getEmail()->isBounced());

        $message = $this->createMessageTo($this->bouncedUser->getEmailAddress());
        $this->resetUserSuppressionMailer->send($message);

        $this->assertFalse($this->bouncedUser->getEmail()->isBounced());
    }

    private function createMessageTo(string $recipientEmail): \Swift_Message
    {
        $message = new \Swift_Message('subject');
        $message
            ->setTo($recipientEmail)
            ->setBody('some message text');

        return $message;
    }
}
