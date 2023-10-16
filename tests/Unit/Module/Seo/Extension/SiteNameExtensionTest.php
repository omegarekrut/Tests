<?php

namespace Tests\Unit\Module\Seo\Extension;

use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\Extension\SiteNameExtension;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;

/**
 * @group seo
 */
class SiteNameExtensionTest extends TestCase
{
    public function testSetDefaultInformation(): void
    {
        $seoPage = new SeoPage();
        $extension = new SiteNameExtension('site name');

        $extension->apply($seoPage, new SeoContext([]));

        $this->assertEquals('site name', $seoPage->getSiteName());
    }
}
