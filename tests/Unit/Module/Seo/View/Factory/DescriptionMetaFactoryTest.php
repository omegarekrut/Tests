<?php

namespace Tests\Unit\Module\Seo\View\Factory;

use App\Module\Seo\TransferObject\SeoPage;
use App\Module\Seo\View\Factory\DescriptionMetaFactory;
use App\Module\Seo\View\TextHelper\DescriptionPreparerHelper;
use Tests\Unit\TestCase;

/**
 * @group seo
 * @group seo-view
 */
class DescriptionMetaFactoryTest extends TestCase
{
    public function testCreation(): void
    {
        $seoPage = new SeoPage();
        $seoPage->setDescription('description');

        $factory = new DescriptionMetaFactory($this->createDescriptionPreparer($seoPage, 'prepared description'));
        $meta = $factory->createMeta($seoPage);

        $this->assertEquals('description', $meta->getName());
        $this->assertEquals('prepared description', $meta->getContent());
        $this->assertEmpty($meta->getProperty());
    }

    public function testEmptyDescription(): void
    {
        $seoPage = new SeoPage();
        $factory = new DescriptionMetaFactory($this->createDescriptionPreparer($seoPage, ''));

        $this->assertEmpty($factory->createMeta($seoPage));

    }

    private function createDescriptionPreparer(SeoPage $seoPage, string $description): DescriptionPreparerHelper
    {
        $stub = $this->createMock(DescriptionPreparerHelper::class);
        $stub
            ->expects($this->once())
            ->method('prepareDescription')
            ->with($seoPage->getDescription())
            ->willReturn($description)
        ;

        return $stub;
    }
}
