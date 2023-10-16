<?php

namespace Tests\Unit\Domain\User\Mail;

use App\Domain\User\Mail\UserEmailConfirmMailFactory;
use Tests\Unit\Helper\TwigEnvironmentTrait;
use Tests\Unit\TestCase;

/**
 * @group public-email
 * @group confirm-mail-email
 */
class UserEmailConfirmMailFactoryTest extends TestCase
{
    use TwigEnvironmentTrait;

    public function testConfirmationMail()
    {
        $user = $this->generateUser();

        $factory = new UserEmailConfirmMailFactory($this->mockTwigEnvironment(
            'mail/registration/confirmation_email.html.twig',
            [
                'user' => $user,
                'host' => 'fishingsib',
            ]
        ), 'fishingsib');

        $swiftMessage = $factory->buildConfirmationMail($user);

        $this->assertEquals('Завершение регистрации на сайте fishingsib', $swiftMessage->getSubject());
        $this->assertEquals([$user->getEmailAddress() => null], $swiftMessage->getTo());
    }
}
