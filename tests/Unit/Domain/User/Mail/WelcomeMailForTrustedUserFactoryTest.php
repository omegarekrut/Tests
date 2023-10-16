<?php

namespace Tests\Unit\Domain\User\Mail;

use App\Domain\User\Entity\User;
use App\Domain\User\Mail\WelcomeMailForTrustedUserFactory;
use Tests\Unit\TestCase;
use Twig\Environment;

/**
 * @group public-email
 */
class WelcomeMailForTrustedUserFactoryTest extends TestCase
{
    private const EXPECTED_EMAIL = 'user@email.com';
    private const EXPECTED_BODY = 'message body';
    private const EXPECTED_SUBJECT = 'Сайт Рыбаков FishingSib: спасибо за регистрацию';
    private const EXPECTED_HOST = 'http://foo.bar';

    public function testBuildMessage(): void
    {
        $user = $this->createUser(self::EXPECTED_EMAIL);
        $renderer = $this->createRenderer('mail/registration/welcome_email.html.twig', $user, self::EXPECTED_HOST, self::EXPECTED_BODY);

        $factory = new WelcomeMailForTrustedUserFactory($renderer, self::EXPECTED_HOST);
        $message = $factory->buildWelcomeMail($user);

        $this->assertEquals(self::EXPECTED_EMAIL, current(array_keys($message->getTo())));
        $this->assertEquals(self::EXPECTED_SUBJECT, $message->getSubject());
        $this->assertEquals(self::EXPECTED_BODY, $message->getBody());
    }

    private function createUser(string $email): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->expects($this->once())
            ->method('getEmailAddress')
            ->willReturn($email);

        return $stub;
    }

    private function createRenderer(string $templateName, User $user, string $host, string $body): Environment
    {
        $stub = $this->createMock(Environment::class);
        $stub
            ->expects($this->once())
            ->method('render')
            ->with($templateName, [
                'user' => $user,
                'host' => $host,
            ])
            ->willReturn($body);

        return $stub;
    }
}
