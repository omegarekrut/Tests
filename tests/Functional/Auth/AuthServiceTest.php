<?php

namespace Tests\Functional\Auth;

use App\Auth\Firewall\Dispatcher;
use App\Auth\AuthService;
use App\Util\Cookie\CookieCollection;
use App\Util\Cookie\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @runTestsInSeparateProcesses @todo Cake under the hood re-initializes session
 * @preserveGlobalState disabled
 */
class AuthServiceTest extends TestCase
{
    private $authService;
    private $user;
    private $tokenStorage;
    private $session;
    private $cookieCollection;
    private $cookieName;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->tokenStorage = $this->getContainer()->get('security.token_storage');
        $this->session = $this->getContainer()->get('session');
        $this->cookieName = 'remember_me';
        $this->cookieCollection = new CookieCollection();
        $this->cookieCollection->add(new Cookie($this->cookieName, 111, '+5 minutes'));

        $this->authService = new AuthService(
            $this->getContainer()->get(Dispatcher::class),
            $this->getContainer()->get('router'),
            $this->tokenStorage,
            'security_logout',
            $this->cookieCollection,
            $this->cookieName
        );
    }

    protected function tearDown(): void
    {
        unset (
            $this->user,
            $this->tokenStorage,
            $this->session,
            $this->authService
        );

        parent::tearDown();
    }

    public function testLogout(): void
    {
        $token = new UsernamePasswordToken($this->user, $this->user->getPassword(), 'main', $this->user->getRoles());
        $this->session->set('_security_main', serialize($token));
        $this->authService->logout();

        $this->assertInstanceOf(AnonymousToken::class, $this->tokenStorage->getToken());
        $this->assertEmpty($this->session->get('_security_main'));
        $this->assertTrue($this->cookieCollection->get($this->cookieName)->isDelete());
    }

    public function testLogin(): void
    {
        $this->authService->login($this->user->getUsername());

        $this->validateToken($this->tokenStorage->getToken());
    }

    public function testLoginWithCookie(): void
    {
        $response = $this->authService->login($this->user->getUsername(), true);
        $this->assertContains('remember_me', array_map(function ($cookie) {
            return $cookie->getName();
        }, $response->headers->getCookies()));

        $this->validateToken($this->tokenStorage->getToken());
    }

    private function validateToken($token): void
    {
        $this->assertTrue($token->isAuthenticated());
        $this->assertEquals($this->user, $token->getUser());
        $this->assertEquals([
            'id' => $this->user->getId(),
            'login' => $this->user->getUsername(),
            'group' => $this->user->getGroup(),
            'forum_user_id' => $this->user->getForumUserId(),
            'created' => $this->user->getCreatedAt()->format('Y-m-d H:i:s'),
            'name' => $this->user->getName(),
            'city' => $this->user->getCity(),
            'birthdate' => null,
        ], $_SESSION['Auth']['User']);
    }
}
