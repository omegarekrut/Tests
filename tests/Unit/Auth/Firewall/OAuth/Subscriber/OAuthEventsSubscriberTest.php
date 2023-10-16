<?php

namespace Tests\Unit\Auth\Firewall\OAuth\Subscriber;

use App\Auth\Firewall\OAuth\Subscriber\OAuthEventsSubscriber;
use App\Domain\User\Entity\User;
use App\HttpMessage\CloseWindowResponse;
use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tests\Unit\TestCase;

/**
 * @group oauth
 */
class OAuthEventsSubscriberTest extends TestCase
{
    private const USER_ID = 1;

    public function testRedirectionToProfileEdit(): void
    {
        $user = $this->createUser(self::USER_ID);
        $event = $this->createEvent($user);

        $expectedUrl = '/profile/edit/social-networks/';
        $urlGenerator = $this->createUrlGenerator($expectedUrl);

        $subscriber = new OAuthEventsSubscriber($urlGenerator);
        $subscriber->onConnected($event);

        $this->assertInstanceOf(CloseWindowResponse::class, $event->getResponse());
        $this->assertEquals($expectedUrl, $event->getResponse()->getRedirectUrl());
    }

    public function testRedirectionToMainPage(): void
    {
        $user = $this->createMock(UserInterface::class);
        $event = $this->createEvent($user);

        $urlGenerator = $this->createUrlGenerator('/');

        $subscriber = new OAuthEventsSubscriber($urlGenerator);
        $subscriber->onConnected($event);

        $this->assertInstanceOf(CloseWindowResponse::class, $event->getResponse());
        $this->assertEquals('/', $event->getResponse()->getRedirectUrl());
    }

    private function createUser(int $userId): User
    {
        $user = $this->createMock(User::class);
        $user
            ->method('getId')
            ->willReturn($userId);

        return $user;
    }

    private function createEvent(UserInterface $user): GetResponseUserEvent
    {
        return new GetResponseUserEvent($user, $this->createMock(Request::class));
    }

    private function createUrlGenerator(string $expectedUrl): UrlGeneratorInterface
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturn($expectedUrl);

        return $urlGenerator;
    }
}
