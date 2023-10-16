<?php

namespace Tests\Traits;

use App\Auth\Visitor\Visitor;
use App\Domain\Region\Entity\Region;
use App\Domain\Seo\Extension\CustomInfoByUriExtension;
use App\Module\Seo\Factory\BreadcrumbsFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait RouteExtensionTrait
{
    abstract protected function createMock(string $originalClassName): MockObject;

    protected function createBreadcrumbsFactoryMock(): BreadcrumbsFactory
    {
        $customInfoByUriExtensionMock = $this->createMock(CustomInfoByUriExtension::class);
        $customInfoByUriExtensionMock->method('withUri')
            ->willReturnSelf();

        return new BreadcrumbsFactory($customInfoByUriExtensionMock);
    }

    protected function createUrlGeneratorMock(): UrlGeneratorInterface
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('/some-link/');

        return $urlGenerator;
    }

    protected function createConfiguredVisitorMock(): Visitor
    {
        return $this->createConfiguredMock(Visitor::class, [
            'getMaterialsRegion' => $this->createConfiguredRegion(),
        ]);
    }

    protected function createConfiguredRegion(): Region
    {
        return $this->createConfiguredMock(Region::class, [
           'getName' => 'Новосибирск',
        ]);
    }
}
