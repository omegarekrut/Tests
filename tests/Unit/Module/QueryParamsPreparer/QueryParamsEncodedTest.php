<?php

namespace Tests\Unit\Module\QueryParamsPreparer;

use App\Module\QueryParamsPreparer\QueryParamsPreparerFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\Unit\TestCase;

class QueryParamsEncodedTest extends TestCase
{
    private const URL_WITH_DECODED_PARAM = '/tidings/?search=%ED%E0+%EA%EE%ED%EA%F3%F0%F1';

    /**
     * @var string[]
     */
    private $originalQueryParams;
    private QueryParamsPreparerFactory $queryParamsPreparerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $getResponseEventWithDecodedUrlParam = $this->createGetResponseEventWithDecodedUrlParam();
        $request = $getResponseEventWithDecodedUrlParam->getRequest();
        $this->originalQueryParams = $request->query->all();
        $this->queryParamsPreparerFactory = new QueryParamsPreparerFactory();
    }

    protected function tearDown(): void
    {
        unset($this->originalQueryParams);
        unset($this->queryParamsPreparerFactory);

        parent::tearDown();
    }

    public function testDecodedUrlParamWillEncoded(): void
    {
        $expectedParams = [
            'search' => 'на конкурс',
        ];

        $this->assertEquals($expectedParams, $this->queryParamsPreparerFactory->getPreparedParams($this->originalQueryParams));
    }

    private function createGetResponseEventWithDecodedUrlParam(): GetResponseEvent
    {
        $request = Request::create(self::URL_WITH_DECODED_PARAM);
        $request->attributes->add(['_route' => 'tidings_list']);

        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
        );
    }
}
