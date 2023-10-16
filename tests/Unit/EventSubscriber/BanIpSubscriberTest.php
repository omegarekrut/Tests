<?php

namespace Tests\Unit\EventSubscriber;

use App\Domain\Ban\Repository\BanIpRepository;
use App\Domain\Ban\Repository\BanUserRepository;
use App\Domain\Ban\Service\Ban;
use App\EventSubscriber\BanIpSubscriber;
use App\Service\ClientIp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\Unit\TestCase;

class BanIpSubscriberTest extends TestCase
{
    /**
     * @dataProvider bannedIpProvider
     */
    public function testBannedIp(string $requestIp, string $banIp): void
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', $requestIp);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $subscriber = new BanIpSubscriber($this->getMockBanRepository($banIp), $this->getMockClientIp($requestStack));
        $getResponseEvent = $this->createGetResponseEvent();

        $subscriber->banIp($getResponseEvent);

        $this->assertEquals(403, $getResponseEvent->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider notBannedIpProvider
     */
    public function testNotBannedIp(string $requestIp, string $banIp): void
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', $requestIp);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $subscriber = new BanIpSubscriber($this->getMockBanRepository($banIp), $this->getMockClientIp($requestStack));
        $getResponseEvent = $this->createGetResponseEvent();

        $subscriber->banIp($getResponseEvent);

        $this->assertNull($getResponseEvent->getResponse());
    }

    private function getMockBanRepository(string $ip): Ban
    {
        $banIpRepositoryMock = $this->getMockBuilder(BanIpRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBannedListIps'])
            ->getMock();

        $banIpRepositoryMock->method('getBannedListIps')
            ->willReturn([$ip]);

        return new Ban(
            $banIpRepositoryMock,
            $this->getMockBuilder(BanUserRepository::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    private function getMockClientIp(RequestStack $requestStack): ClientIp
    {
        return new ClientIp($requestStack);
    }

    private function createGetResponseEvent(): GetResponseEvent
    {
        return new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('/'),
            HttpKernelInterface::MASTER_REQUEST,
        );
    }

    /**
     * @return string[][]
     */
    public function bannedIpProvider(): array
    {
        return [
            'v4' => ['127.0.0.1', '127.0.0.1'],
            'v6' => ['FE80:0000:0000:0000:0202:B3FF:FE1E:8329', 'FE80:0000:0000:0000:0202:B3FF:FE1E:8329'],
            'v4 mask' => ['37.193.118.151', '37.192.0.0/10'],
            'v6 mask' => ['2001:0DB8:ABCD:0012:0000:0000:0000:0001', '2001:db8:abcd:0012::0/80'],
        ];
    }

    /**
     * @return string[][]
     */
    public function notBannedIpProvider(): array
    {
        return [
            'v4' => ['127.0.0.1', '127.0.0.2'],
            'v6' => ['FE80:0000:0000:0000:0202:B3FF:FE1E:8329', 'FE80:0000:0000:0000:0202:B3FF:FE1E:8328'],
            'v4 mask' => ['137.193.118.151', '37.192.0.0/10'],
            'v6 mask' => ['200F:0DB8:ABCD:0012:0000:0000:0000:0001', '2001:db8:abcd:0012::0/80'],
        ];
    }
}
