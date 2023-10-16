<?php

namespace Tests\Unit\Auth\Command;

use App\Auth\AuthService;
use App\Auth\Command\AuthorizeTrustedUserCommand;
use App\Auth\Command\Handler\AuthorizeTrustedUserHandler;
use Tests\Unit\TestCase;

/**
 * @group auth
 */
class AuthorizeTrustedUserHandlerTest extends TestCase
{
    public function testAuthorizeTrustedUser(): void
    {
        $user = $this->generateUser();
        $user->confirmEmail();
        $command = new AuthorizeTrustedUserCommand($user);
        $authService = $this->createMock(AuthService::class);

        // assert here
        $authService
            ->expects($this->once())
            ->method('login')
            ->with($user->getUsername());

        $handler = new AuthorizeTrustedUserHandler($authService);
        $handler->handle($command);
    }

    public function testDoNotAuthorizeUntrustedUser(): void
    {
        $user = $this->generateUser();
        $command = new AuthorizeTrustedUserCommand($user);
        $authService = $this->createMock(AuthService::class);

        // assert here
        $authService
            ->expects($this->never())
            ->method('login');

        $handler = new AuthorizeTrustedUserHandler($authService);
        $handler->handle($command);
    }
}
