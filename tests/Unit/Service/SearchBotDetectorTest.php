<?php

namespace Tests\Unit\Service;

use App\Service\SearchBotDetector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Unit\TestCase;

class SearchBotDetectorTest extends TestCase
{
    public function testIsBot(): void
    {
        $request = new Request();
        $request->headers->set('User-Agent', 'YandexBot');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $clientIpService = new SearchBotDetector($requestStack);

        $this->assertTrue($clientIpService->isBot());
    }

    public function testIsNotBot(): void
    {
        $request = new Request();
        $request->headers->set('User-Agent', 'some-user-agent');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $clientIpService = new SearchBotDetector($requestStack);

        $this->assertFalse($clientIpService->isBot());
    }
}
