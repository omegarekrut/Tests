<?php

namespace Tests\Unit\Module\Seo;

use App\Module\Seo\Exception\ExtensionPropagationException;
use App\Module\Seo\Extension\SeoExtensionInterface;
use App\Module\Seo\ExtensionBasedSeoPageFactory;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;

/**
 * @group seo
 */
class ExtensionBasedSeoPageFactoryTest extends TestCase
{
    public function testApplyExtensions(): void
    {
        $seoPageFactory = new ExtensionBasedSeoPageFactory([
            $this->createExtension('foo'),
            $this->createExtension('bar'),
        ]);

        $seoPage = $seoPageFactory->createFromContext([
            $this,
        ]);

        $this->assertStringContainsString('foo', $seoPage->getTitle());
        $this->assertStringContainsString('bar', $seoPage->getTitle());
    }

    public function testInvalidExtensionType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ExtensionBasedSeoPageFactory([$this]);
    }

    public function testAbortedByExtension(): void
    {
        $blankPage = new SeoPage();

        $seoPageFactory = new ExtensionBasedSeoPageFactory([
            $this->createExtension('bar'),
            $this->createExtension('foo', new ExtensionPropagationException()),
        ]);

        $seoPage = $seoPageFactory->createFromContext([]);
        $this->assertEquals($blankPage, $seoPage);
    }

    private function createExtension(string $appendStringToTitle, ?\Throwable $exception = null): SeoExtensionInterface
    {
        $stub = $this->createMock(SeoExtensionInterface::class);
        $method = $stub
            ->expects($this->once())
            ->method('apply')
            ->willReturnCallback(function (SeoPage $seoPage, iterable $context) use ($appendStringToTitle) {
                $seoPage->setTitle($seoPage->getTitle().$appendStringToTitle);
            })
        ;

        if ($exception) {
            $method->willThrowException($exception);
        }

        return $stub;
    }
}
