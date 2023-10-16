<?php

namespace Tests\Unit\Module\Seo\Extension;

use App\Module\Seo\Extension\CanonicalLink\CanonicalLinkExtension;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Generator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class CanonicalLinkExtensionTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testSetDefaultInformation(UriInterface $uri, array $routeConfiguration, string $expectedLink): void
    {
        $seoPage = new SeoPage();

        $extension = new CanonicalLinkExtension(
            $this->createRequest($uri),
            $routeConfiguration
        );
        $extension->apply($seoPage, new SeoContext([]));

        $this->assertEquals($expectedLink, (string) $seoPage->getCanonicalLink());
    }

    public function getCases(): Generator
    {
        $uri = new Uri('/foo/bar/');
        $uri = $uri->withQuery('parameter1=1&parameter2=2');
        $uri = $uri->withFragment('fragment');

        yield [
            $uri,
            [],
            '/foo/bar/',
        ];

        yield [
            $uri,
            [
                'routes' => [
                    [
                        'match_pattern' => '/^\/foo(.*)/i',
                        'allowed_query_parameters' => [
                            '[parameter1]',
                        ],
                    ],
                ],
            ],
            '/foo/bar/?parameter1=1',
        ];

        yield [
            $uri->withQuery('parameter1=СузуН&parameter2=Игнорировать'),
            [
                'routes' => [
                    [
                        'match_pattern' => '/^\/foo(.*)/i',
                        'allowed_query_parameters' => [
                            '[parameter1]',
                        ],
                    ],
                ],
            ],
            '/foo/bar/?parameter1=%D1%81%D1%83%D0%B7%D1%83%D0%BD',
        ];

        yield [
            $uri->withQuery('parameter[search]=СузуН&parameter2=Игнорировать'),
            [
                'routes' => [
                    [
                        'match_pattern' => '/^\/foo(.*)/i',
                        'allowed_query_parameters' => [
                            '[parameter][search]',
                        ],
                    ],
                ],
            ],
            '/foo/bar/?parameter%5Bsearch%5D=%D1%81%D1%83%D0%B7%D1%83%D0%BD',
        ];

        yield [
            $uri->withPath('/bar/foo/'),
            [
                'routes' => [
                    [
                        'match_pattern' => '/^\/foo(.*)/i',
                        'allowed_query_parameters' => [
                            'parameter1',
                        ],
                    ],
                ],
            ],
            '/bar/foo/',
        ];
    }

    /**
     * @dataProvider getInvalidRouteConfig
     */
    public function testInvalidConfiguration(array $invalidRouteConfig): void
    {
        $this->expectException(InvalidConfigurationException::class);

        new CanonicalLinkExtension($this->createRequest(new Uri()), $invalidRouteConfig);
    }

    public function getInvalidRouteConfig(): array
    {
        return [
            'invalid structure' => [
                [
                    'foo' => 'bar',
                ],
            ],
            'required match_pattern' => [
                'routes' => [
                    [
                        'allowed_query_parameters' => [
                            'parameter1',
                        ],
                    ],
                ],
            ],
            'invalid match pattern' => [
                'routes' => [
                    [
                        'match_pattern' => '///i',
                    ],
                ],
            ],
        ];
    }

    private function createRequest(UriInterface $uri): RequestInterface
    {
        $stub = $this->createMock(RequestInterface::class);
        $stub
            ->method('getUri')
            ->willReturn($uri);

        return $stub;
    }
}
