<?php

namespace Tests\Unit\Domain\Seo\Extension;

use App\Domain\Category\Entity\Category;
use App\Domain\Seo\Extension\CategoryExtension;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\Factory\BreadcrumbsFactory;
use App\Module\Seo\TransferObject\Breadcrumb;
use App\Module\Seo\TransferObject\SeoPage;
use Psr\Http\Message\UriInterface;
use Tests\Unit\TestCase;

/*
 * @group seo
 */
class CategoryExtensionTest extends TestCase
{
    public function testNotApplyByArgument(): void
    {
        $categoryExtension = new CategoryExtension($this->createBreadcrumbsFactoryMock(false));
        $categoryExtension->apply($this->mockSeoPage(true), $this->mockContext());
    }

    public function testNotApplyByCategory(): void
    {
        $categoryExtension = new CategoryExtension($this->createBreadcrumbsFactoryMock(false));
        $categoryExtension->apply($this->mockSeoPage(true), $this->mockContext([new Category('title', 'some category', 'url')]));
    }

    public function testApply(): void
    {
        $rootCategory = new Category('root title', 'root description', 'root-url');
        $subCategory = new Category('sub category title', 'sub category description', 'sub-category-url', $rootCategory);

        $categoryExtension = new CategoryExtension($this->createBreadcrumbsFactoryMock(true));
        $categoryExtension->apply($this->mockSeoPage(false), $this->mockContext([$subCategory]));
    }

    private function createBreadcrumbsFactoryMock($called): BreadcrumbsFactory
    {
        $mock = $this->createMock(BreadcrumbsFactory::class);

        if ($called) {
            $mock->expects($this->once())
                ->method('createByUri')
                ->willReturnCallback(function ($title, UriInterface $uri) {
                    $this->assertEquals('root title', $title);
                    $this->assertEquals('/root-url/', (string) $uri);

                    $breadcrumb = $this->createMock(Breadcrumb::class);
                    $breadcrumb->method('getTitle')->willReturn('breadcrumb title');
                    $breadcrumb->method('getUri')->willReturn($uri);

                    return $breadcrumb;
                });
        } else {
            $mock->expects($this->never())
                ->method('createByUri');
        }

        return $mock;
    }

    private function mockContext(array $context = []): SeoContext
    {
        return new SeoContext($context);
    }

    private function mockSeoPage(bool $emptyParent): SeoPage
    {
        $mock = $this->createMock(SeoPage::class);

        if ($emptyParent) {
            $mock->expects($this->never())
                ->method('addBreadcrumb');
        } else {
            $mock->expects($this->once())
                ->method('addBreadcrumb')
                ->willReturnCallback(function (Breadcrumb $breadcrumb) use ($mock) {
                    $this->assertEquals('breadcrumb title', $breadcrumb->getTitle());
                    $this->assertEquals('/root-url/', $breadcrumb->getUri());

                    return $mock;
                });
        }

        return $mock;
    }
}
