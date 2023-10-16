<?php

namespace Tests\Unit\Util\Security;

use App\Domain\User\Entity\ValueObject\UserRole;
use App\Util\Security\AccessChecker;
use App\Util\Security\Resolver\RequestArgument as RequestArgumentResolver;
use App\Util\Security\Resolver\RequestResource as RequestResourceResolver;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tests\Unit\TestCase;

final class AccessCheckerTest extends TestCase
{
    private UsernamePasswordToken $userPasswordToken;
    private AnonymousToken $anonymousToken;
    private MockObject $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userPasswordToken = new UsernamePasswordToken(
            'user',
            'password',
            'main',
            [
                (string) UserRole::user(),
            ]
        );
        $this->anonymousToken = new AnonymousToken('secret', 'user');
        $this->urlGenerator = $this->createMock(UrlGenerator::class);
        $this->urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->willReturn('/login/?_target_path=/companies/create/');
    }

    public function testAccessGranted(): void
    {
        $expectedRequest = Request::createFromGlobals();
        $expectedResolvedResource = 'resource';

        $accessChecker = new AccessChecker(
            $this->createAuthorizationChecker(true, $expectedResolvedResource),
            $this->createRequestResourceResolver($expectedRequest, $expectedResolvedResource),
            $this->createRequestArgumentResolver([]),
            $this->createTokenStorage($this->userPasswordToken),
            $this->urlGenerator,
        );

        $this->assertNull($accessChecker->checkRequest($expectedRequest));
    }

    public function testAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Доступ запрещен');

        $expectedRequest = Request::createFromGlobals();
        $expectedResolvedResource = 'resource';

        $accessChecker = new AccessChecker(
            $this->createAuthorizationChecker(false, $expectedResolvedResource),
            $this->createRequestResourceResolver($expectedRequest, $expectedResolvedResource),
            $this->createRequestArgumentResolver([]),
            $this->createTokenStorage($this->userPasswordToken),
            $this->urlGenerator,
        );

        $accessChecker->checkRequest($expectedRequest);
    }

    public function testWithArgument(): void
    {
        $expectedRequest = Request::createFromGlobals();
        $expectedResolvedResource = 'resource';
        $expectedArguments = [
            clone $this,
            new \stdClass(),
        ];

        $accessChecker = new AccessChecker(
            $this->createAuthorizationChecker(true, $expectedResolvedResource, $expectedArguments),
            $this->createRequestResourceResolver($expectedRequest, $expectedResolvedResource),
            $this->createRequestArgumentResolver($expectedArguments),
            $this->createTokenStorage($this->userPasswordToken),
            $this->urlGenerator,
        );

        $this->assertNull($accessChecker->checkRequest($expectedRequest));
    }

    public function testRedirectIfAccessDeniedAndUserIsAnonymous(): void
    {
        $expectedRequest = Request::createFromGlobals();
        $expectedResolvedResource = 'resource';

        $accessChecker = new AccessChecker(
            $this->createAuthorizationChecker(false, $expectedResolvedResource),
            $this->createRequestResourceResolver($expectedRequest, $expectedResolvedResource),
            $this->createRequestArgumentResolver([]),
            $this->createTokenStorage($this->anonymousToken),
            $this->urlGenerator,
        );

        $this->assertInstanceOf(RedirectResponse::class, $accessChecker->checkRequest($expectedRequest));
    }

    public function testSecurityDisabled(): void
    {
        $expectedRequest = Request::createFromGlobals();

        $accessChecker = new AccessChecker(
            $this->createAuthorizationChecker(),
            $this->createRequestResourceResolver(),
            $this->createRequestArgumentResolver(),
            $this->createTokenStorage(null),
            $this->urlGenerator,
        );

        $this->assertNull($accessChecker->checkRequest($expectedRequest));
    }

    private function createRequestResourceResolver(
        ?Request $expectedRequest = null,
        ?string $resolvedResource = null
    ): RequestResourceResolver {
        $called = $expectedRequest && $resolvedResource;
        $stub = $this->createMock(RequestResourceResolver::class);

        if ($called) {
            $stub
                ->expects($this->once())
                ->method('getResource')
                ->with($expectedRequest)
                ->willReturn($resolvedResource);
        } else {
            $stub
                ->expects($this->never())
                ->method('getResource')
                ->with($expectedRequest);
        }

        return $stub;
    }

    private function createTokenStorage(?TokenInterface $token = null): TokenStorageInterface
    {
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        return $tokenStorage;
    }

    /**
     * @param mixed[] $subjectArguments
     */
    private function createAuthorizationChecker(
        ?bool $isGrantedResult = null,
        ?string $expectedResource = null,
        ?array $subjectArguments = null
    ): AuthorizationCheckerInterface {
        $stub = $this->createMock(AuthorizationCheckerInterface::class);

        if ($isGrantedResult === null) {
            $stub
                ->expects($this->never())
                ->method('isGranted');

            return $stub;
        }

        if ($subjectArguments !== null) {
            $returnMap = [];

            foreach ($subjectArguments as $key => $argument) {
                $returnMap[] = [
                    $expectedResource,
                    $argument,
                    $isGrantedResult
                ];
            }

            $stub
                ->method('isGranted')
                ->willReturnMap($returnMap);

            return $stub;
        }

        if ($isGrantedResult !== null) {
            $stub
                ->expects($this->once())
                ->method('isGranted')
                ->with($expectedResource)
                ->willReturn($isGrantedResult);
        } else {
            $stub
                ->expects($this->never())
                ->method('isGranted')
                ->with($expectedResource);
        }

        return $stub;
    }

    /**
     * @param mixed[] $arguments
     */
    private function createRequestArgumentResolver(?array $arguments = null): RequestArgumentResolver
    {
        $stub = $this->createMock(RequestArgumentResolver::class);

        if ($arguments !== null) {
            $stub
                ->expects($this->once())
                ->method('getArguments')
                ->willReturn($arguments);
        } else {
            $stub
                ->expects($this->never())
                ->method('getArguments');
        }

        return $stub;
    }
}
