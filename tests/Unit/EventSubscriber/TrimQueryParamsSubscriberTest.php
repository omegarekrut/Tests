<?php

namespace Tests\Unit\EventSubscriber;

use App\EventSubscriber\TrimQueryParamsSubscriber;
use App\Module\QueryParamsPreparer\QueryParamsPreparerFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\Unit\TestCase;

class TrimQueryParamsSubscriberTest extends TestCase
{
    private const SOME_REDIRECT_URL = '/test/?foo=bar';

    private TrimQueryParamsSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new TrimQueryParamsSubscriber($this->createConfiguredMock(UrlGeneratorInterface::class, [
            'generate' => self::SOME_REDIRECT_URL,
        ]), new QueryParamsPreparerFactory());
    }

    protected function tearDown(): void
    {
        unset($this->subscriber);

        parent::tearDown();
    }

    public function testShouldAddRedirectResponseIfGetParameterHaveSpacesAtBeginning(): void
    {
        $urlWithParamIncludeSpacesAtBeginning = 'http://www.fishingsib.loc/tidings/?search=%20%20%20foo';

        $getResponseEventWithGetParameterHaveSpacesAtBeginning = $this->createGetResponseEvent($urlWithParamIncludeSpacesAtBeginning);
        $this->subscriber->trimQueryParams($getResponseEventWithGetParameterHaveSpacesAtBeginning);

        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $getResponseEventWithGetParameterHaveSpacesAtBeginning->getResponse()->getStatusCode());
    }

    public function testShouldAddRedirectResponseIfGetParameterHaveSpacesAtTheEnd(): void
    {
        $urlWithParamIncludeSpacesInTheEnd = 'http://www.fishingsib.loc/tidings/?search=foo%20%20%20';

        $getResponseEventWithGetParameterHaveSpacesAtTheEnd = $this->createGetResponseEvent($urlWithParamIncludeSpacesInTheEnd);
        $this->subscriber->trimQueryParams($getResponseEventWithGetParameterHaveSpacesAtTheEnd);

        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $getResponseEventWithGetParameterHaveSpacesAtTheEnd->getResponse()->getStatusCode());
    }

    public function testShouldAddRedirectResponseIfGetParameterIsDecoded(): void
    {
        $urlWithDecodedParam = 'http://www.fishingsib.loc/tidings/?search=%ED%E0+%EA%EE%ED%EA%F3%F0%F1';

        $getResponseEventWithGetParameterDecoded = $this->createGetResponseEvent($urlWithDecodedParam);
        $this->subscriber->trimQueryParams($getResponseEventWithGetParameterDecoded);

        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $getResponseEventWithGetParameterDecoded->getResponse()->getStatusCode());
    }

    public function testShouldNotAddRedirectResponseIfGetParameterIsValid(): void
    {
        $urlWithValidParam = 'http://www.fishingsib.loc/tidings/?search=baz';

        $getResponseEventWithValidGetParameter = $this->createGetResponseEvent($urlWithValidParam);
        $this->subscriber->trimQueryParams($getResponseEventWithValidGetParameter);

        $this->assertNull($getResponseEventWithValidGetParameter->getResponse());
    }

    public function testShouldNotAddRedirectResponseIfGetParameterIsEmpty(): void
    {
        $urlWithEmptyParam = 'http://www.fishingsib.loc/tidings/';

        $getResponseEventWithEmptyGetParameter = $this->createGetResponseEvent($urlWithEmptyParam);
        $this->subscriber->trimQueryParams($getResponseEventWithEmptyGetParameter);

        $this->assertNull($getResponseEventWithEmptyGetParameter->getResponse());
    }

    private function createGetResponseEvent(string $url): GetResponseEvent
    {
        $request = Request::create($url);
        $request->attributes->add(['_route' => 'tidings_list']);

        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
        );
    }
}
