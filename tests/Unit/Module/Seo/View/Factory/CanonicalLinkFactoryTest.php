<?php

namespace Tests\Unit\Module\Seo\View\Factory;

use App\Module\Seo\TransferObject\SeoPage;
use App\Module\Seo\View\Factory\CanonicalLinkFactory;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 * @group seo-view
 */
class CanonicalLinkFactoryTest extends TestCase
{
    public function testCreation(): void
    {
        $seoPage = new SeoPage();
        $canonicalLinkFactory = new CanonicalLinkFactory();

        $this->assertEmpty($canonicalLinkFactory->createLink($seoPage));

        $seoPage->setCanonicalLink(new Uri('http://canonical.link'));
        $link = $canonicalLinkFactory->createLink($seoPage);

        $this->assertEquals('canonical', $link->getRel());
        $this->assertEquals('http://canonical.link', $link->getHref());
    }
}
