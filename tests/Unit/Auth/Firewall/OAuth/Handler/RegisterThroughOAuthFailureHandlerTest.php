<?php

namespace Tests\Unit\Auth\Firewall\OAuth\Handler;

use App\Auth\Firewall\OAuth\Exception\AccountNotLinkedException;
use App\Auth\Firewall\OAuth\Handler\RegisterThroughOAuthFailureHandler;
use App\Module\OAuth\Entity\OAuthUserInformation;
use App\Module\OAuth\Storage\SessionOAuthUserStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Tests\Unit\TestCase;

/**
 * @group oauth
 */
class RegisterThroughOAuthFailureHandlerTest extends TestCase
{
    /** @var Request */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new Request();
        $this->request->setSession(new Session(new MockArraySessionStorage()));
    }

    public function testSaveUserInformation(): void
    {
        $oauthUserInformation = $this->createMock(OAuthUserInformation::class);
        $sessionOAuthUserStorage = $this->createSessionOAuthUserStorage($oauthUserInformation);
        $exception = $this->createAccountNotLinkedException($oauthUserInformation);

        $handler = new RegisterThroughOAuthFailureHandler($sessionOAuthUserStorage);
        $response = $handler->onAuthenticationFailure($this->request, $exception);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/users/register/oauth/', $response->headers->get('location'));
    }

    private function createSessionOAuthUserStorage(OAuthUserInformation $authUserInformation): SessionOAuthUserStorage
    {
        $stub = $this->createMock(SessionOAuthUserStorage::class);
        $stub
            ->method('setUserInformation')
            ->with($authUserInformation);

        return $stub;
    }

    private function createAccountNotLinkedException(OAuthUserInformation $authUserInformation): AccountNotLinkedException
    {
        $stub = $this->createMock(AccountNotLinkedException::class);
        $stub
            ->method('getUserInformation')
            ->willReturn($authUserInformation);

        return $stub;
    }
}
