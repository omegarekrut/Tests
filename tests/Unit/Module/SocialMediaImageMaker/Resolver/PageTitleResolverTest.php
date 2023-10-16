<?php

namespace Tests\Unit\Module\SocialMediaImageMaker\Resolver;

use App\Module\SocialMediaImageMaker\Resolver\PageTitleResolver;
use App\Module\SocialMediaImageMaker\SocialPage;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\Functional\TestCase;

class PageTitleResolverTest extends TestCase
{
    public function testGetTitleFromSocialPageUrl(): void
    {
        $client = $this->createClientForReturnResponse(new Response(200, [], '<h1>Title from h1</h1>'));

        $pageTitleResolver = new PageTitleResolver($client, 'http://example.mock', 'default title');

        $socialPage = new SocialPage('http://example.mock/default', '', []);
        $title = $pageTitleResolver->resolveFromPageSource($socialPage);

        $this->assertEquals('Title from h1', $title);
    }

    public function testDefaultTitleMustBeResolvedForPageWithoutTitle(): void
    {
        $expectedDefaultTitle = 'default title';

        $client = $this->createClientForReturnResponse(new Response(200));

        $pageTitleResolver = new PageTitleResolver($client, 'http://example.mock', $expectedDefaultTitle);

        $socialPage = new SocialPage('http://example.mock/default', '', []);
        $title = $pageTitleResolver->resolveFromPageSource($socialPage);

        $this->assertEquals($expectedDefaultTitle, $title);
    }

    private function createClientForReturnResponse(Response $response): ClientInterface
    {
        $mockHandler = new MockHandler([$response]);
        $handlerStack = HandlerStack::create($mockHandler);

        return new Client(['handler' => $handlerStack]);
    }
}
