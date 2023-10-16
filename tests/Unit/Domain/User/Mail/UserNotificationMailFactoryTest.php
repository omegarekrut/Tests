<?php

namespace Tests\Unit\Domain\User\Mail;

use App\Domain\User\Entity\User;
use App\Domain\User\Generator\SubscribeNewsletterHashGenerator;
use App\Domain\User\Mail\UserNotificationMailFactory;
use App\Twig\User\AvatarPathGenerator;
use App\Domain\User\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\Unit\Helper\TwigEnvironmentTrait;
use Tests\Unit\TestCase;

/**
 * @group public-email
 * @group notification-mail-email
 */
class UserNotificationMailFactoryTest extends TestCase
{
    use TwigEnvironmentTrait;

    public function testBuildMail(): void
    {
        $user = $this->createUserMock();

        $factory = new UserNotificationMailFactory(
            $this->mockTwigEnvironment(
                'mail/notification/notification_email.html.inky.twig',
                [
                    'title' => 'Ваши новые уведомления на Fishingsib.ru',
                    'notificationViews' => [],
                    'imageContentIds' => [],
                    'unsubscribeLink' => 'unsubscribe-link',
                ], 'notification template'
            ),
            'path',
            $this->createUrlGeneratorMock(),
            $this->createMock(AvatarPathGenerator::class),
            $this->createMock(SubscribeNewsletterHashGenerator::class),
            $this->createMock(UserRepository::class)
        );

        $swiftMessage = $factory->buildMail($user->getEmailAddress(), $user->getId(), []);

        $this->assertEquals('Ваши новые уведомления на Fishingsib.ru', $swiftMessage->getSubject());
        $this->assertEquals([$user->getEmailAddress() => null], $swiftMessage->getTo());
        $this->assertEquals('notification template', $swiftMessage->getBody());
    }

    private function createUserMock(): User
    {
        $mock = $this->createMock(User::class);

        $mock
            ->method('getId')
            ->willReturn(1);

        $mock
            ->method('getEmailAddress')
            ->willReturn('test@test.com');

        return $mock;
    }

    private function createUrlGeneratorMock(): UrlGeneratorInterface
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('unsubscribe-link');

        return $urlGenerator;
    }
}
