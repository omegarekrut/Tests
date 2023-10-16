<?php

namespace Tests\Unit\Bridge\Forum\Provider;

use App\Bridge\Xenforo\Provider\Api\ClientApi;
use PHPUnit\Framework\MockObject\MockObject;

trait ClientApiTrait
{
    abstract protected function createMock(string $originalClassName): MockObject;
    abstract public function once();

    private function createClientApi(string $expectedUri, ?array $expectedArguments = null, array $result = []): ClientApi
    {
        $client = $this->createMock(ClientApi::class);
        $client
            ->expects($this->once())
            ->method('handle')
            ->with($expectedUri, $expectedArguments)
            ->willReturn($result);

        return $client;
    }
}
