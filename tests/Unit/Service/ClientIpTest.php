<?php

namespace Tests\Unit\Service;

use App\Service\ClientIp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Unit\TestCase;

class ClientIpTest extends TestCase
{
    private const CLIENT_IP = '127.0.0.42';
    private const CLOUD_FLARE_FORWARDED_IP = '127.0.0.43';
    private const LOCAL_IP = '127.0.0.1';
    private const PROXY_IP = '192.0.0.1';

    public function testSimpleDetect(): void
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', self::CLIENT_IP);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $clientIpService = new ClientIp($requestStack);

        $this->assertEquals(self::CLIENT_IP, $clientIpService->getIp());
    }

    public function testCloudFlareDetect(): void
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', self::CLIENT_IP);
        $request->headers->set('HTTP_CF_CONNECTING_IP', self::CLOUD_FLARE_FORWARDED_IP);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $clientIpService = new ClientIp($requestStack);

        $this->assertEquals(self::CLOUD_FLARE_FORWARDED_IP, $clientIpService->getIp());
    }

    public function testFromCLI(): void
    {
        $requestStack = new RequestStack();

        $clientIpService = new ClientIp($requestStack);

        $this->assertEquals(self::LOCAL_IP, $clientIpService->getIp());
    }

    public function testTrustedProxies(): void
    {
        $_SERVER['Resolventa-X-Forwarded-For'] = self::CLIENT_IP;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $_SERVER['Resolventa-X-Forwarded-For'];

        Request::setTrustedProxies([self::PROXY_IP], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);

        $request = Request::createFromGlobals();
        $request->server->set('REMOTE_ADDR', self::PROXY_IP);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $clientIpService = new ClientIp($requestStack);

        $this->assertEquals(self::CLIENT_IP, $clientIpService->getIp());
    }
}
