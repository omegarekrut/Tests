<?php

namespace Tests\Unit\EventSubscriber;

use App\EventSubscriber\DoubleSlashesInEndPathSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\Unit\TestCase;

class DoubleSlashesInEndPathSubscriberTest extends TestCase
{
    public function testShouldAddRedirectResponseIfPathEndsWithDoubleSlashes(): void
    {
        $subscriber = new DoubleSlashesInEndPathSubscriber();

        $getResponseEventWithDoubleSlashes = $this->createGetResponseEventWithDoubleSlashes();
        $subscriber->deleteDoubleSlashesInEndPath($getResponseEventWithDoubleSlashes);

        $this->assertEquals('302', $getResponseEventWithDoubleSlashes->getResponse()->getStatusCode());
        $this->assertEquals('/page/', $getResponseEventWithDoubleSlashes->getResponse()->headers->get('location'));
    }

    public function testShouldSkipEventIfPathDoesNotEndWithDoubleSlashes(): void
    {
        $subscriber = new DoubleSlashesInEndPathSubscriber();

        $getResponseEventWithoutDoubleSlashes = $this->createGetResponseEventWithoutDoubleSlashes();
        $subscriber->deleteDoubleSlashesInEndPath($getResponseEventWithoutDoubleSlashes);

        $this->assertNull($getResponseEventWithoutDoubleSlashes->getResponse());
    }

    private function createGetResponseEventWithDoubleSlashes(): GetResponseEvent
    {
        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://example.com/page//'),
            HttpKernelInterface::MASTER_REQUEST,
        );
    }

    private function createGetResponseEventWithoutDoubleSlashes(): GetResponseEvent
    {
        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://example.com/page/'),
            HttpKernelInterface::MASTER_REQUEST,
        );
    }
}
