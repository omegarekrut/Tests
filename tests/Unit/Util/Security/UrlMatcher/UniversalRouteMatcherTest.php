<?php

namespace Tests\Unit\Util\Security\UrlMatcher;

use App\Util\Security\UrlMatcher\UniversalRouteMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Tests\Unit\TestCase;

class UniversalRouteMatcherTest extends TestCase
{
    /**
     * @var UniversalRouteMatcher
     */
    private $universalRouteMatcher;

    /**
     * @var RequestContext
     */
    private $requestContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestContext = $this->createMock(RequestContext::class);
        $this->universalRouteMatcher = new UniversalRouteMatcher();
    }

    public function testFindFirstValidInStack()
    {
        $request = Request::create('foo/bar');
        $matchResultFixture = [
            '_controller' => 'foo:bar',
        ];

        // Первый вызовит исключение
        $firstMatcher = new UrlMatcherMock();

        // Второй должен быть результатом
        $secondMatcher = (new UrlMatcherMock())->setMatchResult(
            function () use ($matchResultFixture) {
                return $matchResultFixture;
            }
        );

        // Трейти вызвал бы исключение, если до него дошел бы стек
        $thirdMatcher = new UrlMatcherMock();

        $this->universalRouteMatcher
            ->add($firstMatcher)
            ->add($secondMatcher)
            ->add($thirdMatcher);

        $this->assertEquals($matchResultFixture, $this->universalRouteMatcher->matchRequest($request));
    }

    public function testNotFoundResource()
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->universalRouteMatcher
            ->add(new UrlMatcherMock())
            ->add(new UrlMatcherMock());
        $request = Request::create('foo/bar');

        $this->universalRouteMatcher->matchRequest($request);
    }

    public function testEmptyUrlMatcherStack()
    {
        $this->expectException(ResourceNotFoundException::class);

        $request = Request::create('foo/bar');
        $this->universalRouteMatcher->matchRequest($request);
    }

    public function testNotSupportResource()
    {
        $this->expectException(MethodNotAllowedException::class);

        $urlMatcherWithNotSupportResource = new UrlMatcherMock();
        $urlMatcherWithNotSupportResource->setMatchResult(
            function () {
                throw new MethodNotAllowedException(
                    [
                        'GET',
                        'POST',
                    ]
                );
            }
        );

        $this->universalRouteMatcher
            ->add(new UrlMatcherMock())
            ->add($urlMatcherWithNotSupportResource);

        $request = Request::create('foo/bar');

        $this->universalRouteMatcher->matchRequest($request);
    }
}
