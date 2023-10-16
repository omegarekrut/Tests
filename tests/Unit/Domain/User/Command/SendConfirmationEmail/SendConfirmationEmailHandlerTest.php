<?php

namespace Tests\Unit\Domain\User\Command\SendConfirmationEmail;

use App\Domain\User\Command\SendConfirmationEmail\Handler\SendConfirmationEmailHandler;
use App\Domain\User\Command\SendConfirmationEmail\SendConfirmationEmailCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Token;
use App\Domain\User\Mail\UserEmailConfirmMailFactory;
use App\Domain\User\Service\Util\TokenGenerator;
use Swift_Message;
use Tests\Unit\Mock\MailerMock;
use Tests\Unit\Mock\RequiredUserEmailMailerResolverMock;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

/**
 * @group registration
 */
class SendConfirmationEmailHandlerTest extends TestCase
{
    private const TOKEN = 'token';
    /**
     * @var User
     */
    private $user;
    /**
     * @var SendConfirmationEmailCommand
     */
    private $command;
    /**
     * @var ObjectManagerMock
     */
    private $objectManager;
    /**
     * @var \Swift_Message
     */
    private $message;
    /**
     * @var MailerMock
     */
    private $mailer;
    /**
     * @var SendConfirmationEmailHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->generateUser();
        $this->command = new SendConfirmationEmailCommand($this->user);

        $this->objectManager = new ObjectManagerMock();
        $tokenGenerator = $this->createMock(TokenGenerator::class);
        $tokenGenerator
            ->method('generateToken')
            ->willReturn(self::TOKEN);
        $this->message = $this->createMock(Swift_Message::class);
        $userEmailConfirmMailFactory = $this->createMock(UserEmailConfirmMailFactory::class);
        $userEmailConfirmMailFactory
            ->method('buildConfirmationMail')
            ->willReturn($this->message);

        $this->mailer = new MailerMock();
        $requiredUserEmailMailerResolver = new RequiredUserEmailMailerResolverMock($this->mailer);

        $this->handler = new SendConfirmationEmailHandler($this->objectManager, $tokenGenerator, $userEmailConfirmMailFactory, $requiredUserEmailMailerResolver);
    }

    public function testConfirmationEmailIsSent(): void
    {
        $this->handler->handle($this->command);

        $this->assertEquals($this->message, $this->mailer->getLastSentMessage());
    }

    public function testConfirmationTokenIsSaved(): void
    {
        $this->handler->handle($this->command);
        $savedUser = $this->objectManager->getLastPersistedObject();

        $this->assertEquals(self::TOKEN, $savedUser->getEmail()->getConfirmationToken()->getToken());
    }

    public function testDoNotSendConfirmationIfEmailIsConfirmed(): void
    {
        $this->user->confirmEmail();
        $command = new SendConfirmationEmailCommand($this->user);
        $this->handler->handle($command);

        $this->assertEquals(null, $this->objectManager->getLastPersistedObject());
        $this->assertEquals(null, $this->mailer->getLastSentMessage());
    }

    public function testDoNotSendDuplicateConfirmationEmail(): void
    {
        $token = $this->createMock(Token::class);
        $token
            ->method('isValidToken')
            ->willReturn(true);
        $this->user->getEmail()->setConfirmationToken($token);

        $command = new SendConfirmationEmailCommand($this->user);
        $this->handler->handle($command);

        $this->assertEquals(null, $this->objectManager->getLastPersistedObject());
        $this->assertEquals(null, $this->mailer->getLastSentMessage());
    }
}
