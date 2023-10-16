<?php

namespace Tests\Unit\Domain\User\Mail;

use App\Domain\User\Command\ResetPassword\SendResetPasswordMailCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Token;
use App\Domain\User\Mail\ResetPasswordMailFactory;
use App\Domain\User\Repository\UserRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\Unit\Helper\TwigEnvironmentTrait;
use Tests\Unit\TestCase;

/**
 * @group reset-password
 * @group public-email
 */
class ResetPasswordMailFactoryTest extends TestCase
{
    use ProphecyTrait;
    use TwigEnvironmentTrait;

    public function testBuildResetPasswordMail()
    {
        $user = $this->getMockUser();
        $mailFactory = new ResetPasswordMailFactory($this->mockTwigEnvironment(
            'mail/password/reset.html.twig',
            [
                'user' => $user,
                'host' => 'fishingsib',
            ],
            'Twig template'
        ), 'fishingsib', $this->getMockUserRepository($user));

        $command = new SendResetPasswordMailCommand();
        $command->loginOrEmail = 'test@test.com';

        $swiftMessage = $mailFactory->buildResetPasswordMail($command);

        $this->assertEquals('Восстановление пароля на сайте fishingsib', $swiftMessage->getSubject());
        $this->assertEquals(['test@test.com' => null], $swiftMessage->getTo());
        $this->assertEquals('Twig template', $swiftMessage->getBody());
    }

    private function getMockUserRepository(User $user): UserRepository
    {
        $mock = $this->prophesize(UserRepository::class);

        $mock
            ->findOneByLoginOrEmail(Argument::any())
            ->willReturn($user);

        return $mock->reveal();
    }

    private function getMockUser(): User
    {
        $mock = $this->prophesize(User::class);

        $mock
            ->getLogin()
            ->willReturn('login');

        $mock
            ->getResetPasswordToken()
            ->willReturn(new Token('token'));

        $mock
            ->getEmailAddress()
            ->willReturn('test@test.com');

        return $mock->reveal();
    }
}
