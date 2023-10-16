<?php

namespace Tests\Unit\Module\Seo\Factory;

use App\Domain\Seo\Extension\CustomInfoByUriExtension;
use App\Module\Seo\Factory\BreadcrumbsFactory;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

class BreadcrumbsFactoryTest extends TestCase
{
    public function testCreateByUriWithRewriteH1()
    {
        $breadcrumbsFactory = new BreadcrumbsFactory($this->customInfoByUriMock(true));

        $breadcrumbs = $breadcrumbsFactory->createByUri('title', new Uri('/custom-url/'));

        $this->assertEquals('/custom-url/', $breadcrumbs->getUri());
        $this->assertEquals('new title', $breadcrumbs->getTitle());
    }

    public function testCreateByUriWithoutRewriteH1()
    {
        $breadcrumbsFactory = new BreadcrumbsFactory($this->customInfoByUriMock(false));

        $breadcrumbs = $breadcrumbsFactory->createByUri('title', new Uri('/custom-url/'));

        $this->assertEquals('/custom-url/', $breadcrumbs->getUri());
        $this->assertEquals('title', $breadcrumbs->getTitle());
    }

    private function customInfoByUriMock($isRewrite): CustomInfoByUriExtension
    {
        $mock = $this->createMock(CustomInfoByUriExtension::class);

        $mock
            ->expects($this->once())
            ->method('withUri')
            ->with('/custom-url/')
            ->willReturn($mock);

        $mock
            ->expects($this->once())
            ->method('apply')
            ->willReturnCallback(function (SeoPage $seoPage) use ($isRewrite) {
                if ($isRewrite) {
                    $seoPage->setH1('new title');
                }
            });

        return $mock;
    }
}
