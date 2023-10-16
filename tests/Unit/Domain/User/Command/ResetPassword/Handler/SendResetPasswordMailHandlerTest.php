<?php

namespace Tests\Unit\Domain\User\Command\ResetPassword\Handler;

use App\Domain\User\Command\ResetPassword\Handler\SendResetPasswordMailHandler;
use App\Domain\User\Command\ResetPassword\SendResetPasswordMailCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Mail\ResetPasswordMailFactory;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Service\Util\TokenGenerator;
use Swift_Message;
use Tests\Unit\Mock\MailerMock;
use Tests\Unit\Mock\RequiredUserEmailMailerResolverMock;
use Tests\Unit\TestCase;

/**
 * @group reset-password
 */
class SendResetPasswordMailHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $mailer = new MailerMock();
        $requiredUserEmailMailerResolver = new RequiredUserEmailMailerResolverMock($mailer);
        $tokenGenerator = new TokenGenerator();
        $swiftMessage = new Swift_Message();

        $handler = new SendResetPasswordMailHandler(
            $requiredUserEmailMailerResolver,
            $this->getMockResetPasswordMailFactory($swiftMessage),
            $this->getMockUserRepository(),
            $tokenGenerator
        );

        $command = new SendResetPasswordMailCommand();
        $command->loginOrEmail = 'login';

        $handler->handle($command);

        $this->assertEquals($swiftMessage, $mailer->getLastSentMessage());
    }

    private function getMockResetPasswordMailFactory(Swift_Message $message): ResetPasswordMailFactory
    {
        $stub = $this->createMock(ResetPasswordMailFactory::class);

        $stub
            ->method('buildResetPasswordMail')
            ->willReturn($message);

        return $stub;
    }

    private function getMockUserRepository(): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);

        $stub
            ->expects($this->once())
            ->method('findOneByLoginOrEmail')
            ->with('login')
            ->willReturn($this->getMockUser());

        $stub
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($entity) {
                $this->assertInstanceOf(User::class, $entity);
            });

        return $stub;
    }

    private function getMockUser(): User
    {
        $stub = $this->createMock(User::class);

        return $stub;
    }
}
