<?php

namespace Tests\Unit\Domain\User\Command\SendWelcomeEmail;

use App\Domain\User\Command\SendWelcomeEmail\Handler\SendWelcomeEmailForTrustedUserHandler;
use App\Domain\User\Command\SendWelcomeEmail\SendWelcomeEmailForTrustedUserCommand;
use App\Domain\User\Mail\WelcomeMailForTrustedUserFactory;
use Swift_Message;
use Tests\Unit\Mock\MailerMock;
use Tests\Unit\Mock\RequiredUserEmailMailerResolverMock;
use Tests\Unit\TestCase;

/**
 * @group registration
 * @group auth
 */
class SendWelcomeEmailForTrustedUserHandlerTest extends TestCase
{
    private $user;
    private $command;
    private $message;
    private $handler;
    private $mailer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->generateUser();
        $this->command = new SendWelcomeEmailForTrustedUserCommand($this->user);

        $this->message = $this->createMock(Swift_Message::class);
        $welcomeMailFactory = $this->createMock(WelcomeMailForTrustedUserFactory::class);
        $welcomeMailFactory
            ->method('buildWelcomeMail')
            ->willReturn($this->message);

        $this->mailer = new MailerMock();
        $requiredUserEmailMailerResolver = new RequiredUserEmailMailerResolverMock($this->mailer);

        $this->handler = new SendWelcomeEmailForTrustedUserHandler($welcomeMailFactory, $requiredUserEmailMailerResolver);
    }

    public function testWelcomeEmailForTrustedUserIsSent(): void
    {
        $this->user->confirmEmail();
        $this->handler->handle($this->command);

        $this->assertSame($this->message, $this->mailer->getLastSentMessage());
    }

    public function testWelcomeEmailForUntrustedUserIsNotSent(): void
    {
        $this->handler->handle($this->command);

        $this->assertEquals(null, $this->mailer->getLastSentMessage());
    }
}
