<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\ExcludeRoutesExtension;
use App\Module\Seo\Exception\ExtensionPropagationException;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

class ExcludeRoutesExtensionTest extends TestCase
{
    /**
     * @dataProvider getRouteNameForExclude
     */
    public function testSupportedForExclude(string $routeName): void
    {
        $this->expectException(ExtensionPropagationException::class);

        $route = new Route($routeName, new Uri(''));
        $extension = new ExcludeRoutesExtension();

        $this->assertTrue($extension->isSupported($route));

        $extension->apply(new SeoPage(), new SeoContext([]));
    }

    public function getRouteNameForExclude(): array
    {
        return [
            'admin routes' => [
                'admin_foo',
            ],
            'forum route' => [
                'forum_template',
            ],
        ];
    }

    /**
     * @dataProvider getRouteName
     */
    public function testUnsupportedForExclude(string $routeName): void
    {
        $route = new Route($routeName, new Uri(''));
        $extension = new ExcludeRoutesExtension();

        $this->assertFalse($extension->isSupported($route));
    }

    public function getRouteName(): array
    {
        return [
            'contains admin word' => [
                'foo_admin',
            ],
            'some page route' => [
                'page_display',
            ],
        ];
    }
}
