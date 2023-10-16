<?php

namespace Tests\Unit\EventSubscriber;

use App\EventSubscriber\SubdomainSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\Unit\TestCase;

class SubdomainSubscriberTest extends TestCase
{
    private const DOMAIN = 'fishingsib.ru';
    private const SUB_DOMAIN = 'www';
    private const SCHEMA = 'http';

    private string $expectedLocation;

    private SubdomainSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->expectedLocation = sprintf('http://%s.%s/', self::SUB_DOMAIN, self::DOMAIN);
        $this->subscriber = new SubdomainSubscriber(self::DOMAIN, self::SUB_DOMAIN, self::SCHEMA);
    }

    public function testRedirectIfRequestHasWrongBaseSubdomain(): void
    {
        $getResponseEventWithWrongBaseSubdomain = $this->createGetResponseEventWithWrongBaseSubdomain();
        $this->subscriber->redirectWithActiveSubdomain($getResponseEventWithWrongBaseSubdomain);

        $this->assertEquals($this->expectedLocation, $getResponseEventWithWrongBaseSubdomain->getResponse()->headers->get('location'));
    }

    public function testRedirectIfRequestHasWrongDomain(): void
    {
        $getResponseEventWithWrongDomain = $this->createGetResponseEventWithWrongDomain();
        $this->subscriber->redirectWithActiveSubdomain($getResponseEventWithWrongDomain);

        $this->assertEquals($this->expectedLocation, $getResponseEventWithWrongDomain->getResponse()->headers->get('location'));
    }

    public function testIfRequestHasCorrectUri(): void
    {
        $getResponseEventWithCorrectUri = $this->createGetResponseEventWithCorrectUri();
        $this->subscriber->redirectWithActiveSubdomain($getResponseEventWithCorrectUri);

        $this->assertNull($getResponseEventWithCorrectUri->getResponse());
    }

    public function testHttpsRedirect(): void
    {
        $httpsSchema = 'https';

        $expectedLocation = sprintf('%s://%s.%s/', $httpsSchema, self::SUB_DOMAIN, self::DOMAIN);
        $subscriber = new SubdomainSubscriber(self::DOMAIN, self::SUB_DOMAIN, $httpsSchema);

        $getResponseEventWithWrongBaseSubdomain = $this->createGetResponseEventWithWrongBaseSubdomain();

        $subscriber->redirectWithActiveSubdomain($getResponseEventWithWrongBaseSubdomain);

        $this->assertEquals($expectedLocation, $getResponseEventWithWrongBaseSubdomain->getResponse()->headers->get('location'));
    }

    private function createGetResponseEventWithWrongBaseSubdomain(): GetResponseEvent
    {
        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://w2ww.fishingsib.ru/'),
            HttpKernelInterface::MASTER_REQUEST,
        );
    }

    private function createGetResponseEventWithWrongDomain(): GetResponseEvent
    {
        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('http://www.fishingsibbb.ru/'),
            HttpKernelInterface::MASTER_REQUEST,
        );
    }

    private function createGetResponseEventWithCorrectUri(): GetResponseEvent
    {
        $correctUri = sprintf('http://%s.%s/', self::SUB_DOMAIN, self::DOMAIN);

        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create($correctUri),
            HttpKernelInterface::MASTER_REQUEST,
        );
    }
}
