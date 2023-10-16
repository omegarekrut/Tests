<?php

namespace Tests\Functional\Auth\Firewall;

use App\Auth\AuthService;
use App\Auth\Firewall\Dispatcher;
use App\Auth\Firewall\AuthenticationError\AuthenticationExceptionInSessionStorage;
use App\Auth\Firewall\AuthenticationError\AuthenticationExceptionSerializer;
use App\Auth\Firewall\AuthenticationEventsSubscriber;
use App\Util\Cookie\CookieCollection;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\Functional\TestCase;

/**
 * @group auth
 */
class AuthenticationEventsSubscriberTest extends TestCase
{
    /** @var TokenInterface */
    private $token;
    /** @var AuthenticationEventsSubscriber  */
    private $subscriber;
    /** @var AuthenticationExceptionInSessionStorage */
    private $authenticationExceptionStorage;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $session = new Session(new MockArraySessionStorage());
        $exceptionSerializer = new AuthenticationExceptionSerializer();
        $this->authenticationExceptionStorage = new AuthenticationExceptionInSessionStorage($exceptionSerializer, $session);

        $this->token = $this->createMock(TokenInterface::class);
        $this->tokenStorage = $this->getContainer()->get('security.token_storage');
        $this->tokenStorage->setToken(new UsernamePasswordToken('user', 'password', 'virtual', [
            'role',
        ]));

        $this->subscriber = new AuthenticationEventsSubscriber($this->authenticationExceptionStorage, $this->createAuthService($this->tokenStorage));
    }

    protected function tearDown(): void
    {
        unset(
            $this->authenticationExceptionStorage,
            $this->token,
            $this->tokenStorage,
            $this->subscriber
        );

        parent::tearDown();
    }

    public function testExceptionShouldBeMovedToStorageAfterHandlingEvent(): void
    {
        $expectedException = new BadCredentialsException('expected message key');

        $this->subscriber->moveExceptionToAuthenticationExceptionStorage(new AuthenticationFailureEvent($this->token, $expectedException));

        $actualException = $this->authenticationExceptionStorage->getException();

        $this->assertInstanceOf(get_class($expectedException), $actualException);
        $this->assertEquals($expectedException->getMessage(), $actualException->getMessage());
    }

    public function testActiveUserShouldNotBeLogoutAfterHandlingEvent(): void
    {
        $this->subscriber->logoutDisabledUser(new AuthenticationFailureEvent($this->token, new BadCredentialsException()));

        $this->assertInstanceOf(UsernamePasswordToken::class, $this->tokenStorage->getToken());
    }

    public function testDisabledUserShouldBeLogoutAfterHandlingEvent(): void
    {
        $this->subscriber->logoutDisabledUser(new AuthenticationFailureEvent($this->token, new DisabledException()));

        $this->assertInstanceOf(AnonymousToken::class, $this->tokenStorage->getToken());
    }

    public function createAuthService(TokenStorageInterface $tokenStorage): AuthService
    {
        $authService = new AuthService(
            $this->createMock(Dispatcher::class),
            $this->createMock(UrlGeneratorInterface::class),
            $tokenStorage,
            'security_logout',
            $this->createMock(CookieCollection::class),
            'cookie'
        );

        return $authService;
    }
}
