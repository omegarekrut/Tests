<?php

namespace Tests\Unit\EventSubscriber;

use App\EventSubscriber\SchemaSubscriber;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\Unit\TestCase;

class SchemaSubscriberTest extends TestCase
{
    private const EXPECTED_ACTIVE_SCHEMA = 'https';

    public function testSeeExceptionIfInvalidActiveSchema(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid scheme setting');

        new SchemaSubscriber('invalid');
    }

    public function testRedirectWithActiveSchema(): void
    {
        $subscriber = new SchemaSubscriber(self::EXPECTED_ACTIVE_SCHEMA);

        $getResponseEventWithHttps = $this->createGetResponseEventWithoutActiveSchema();
        $subscriber->redirectWithActiveSchema($getResponseEventWithHttps);
        $expectedLocation = sprintf('%s://%s', self::EXPECTED_ACTIVE_SCHEMA, 'example.com/');

        $this->assertEquals($expectedLocation, $getResponseEventWithHttps->getResponse()->headers->get('location'));
    }

    public function testWithoutRedirect(): void
    {
        $subscriber = new SchemaSubscriber(self::EXPECTED_ACTIVE_SCHEMA);

        $getResponseEventWithoutActiveSchema = $this->createGetResponseEventWithActiveSchema();
        $subscriber->redirectWithActiveSchema($getResponseEventWithoutActiveSchema);

        $this->assertNull($getResponseEventWithoutActiveSchema->getResponse());
    }

    private function createGetResponseEventWithoutActiveSchema(): GetResponseEvent
    {
        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('http://example.com/'),
            HttpKernelInterface::MASTER_REQUEST,
        );
    }

    private function createGetResponseEventWithActiveSchema(): GetResponseEvent
    {
        $uriWithActiveSchema = sprintf('%s://%s', self::EXPECTED_ACTIVE_SCHEMA, 'example.com/');

        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create($uriWithActiveSchema),
            HttpKernelInterface::MASTER_REQUEST,
        );
    }
}
