<?php

namespace Tests\Functional\Domain\SocialMediaImageMaker;

use App\Domain\SocialMediaImageMaker\SocialPageFactory;
use Tests\Functional\TestCase;

class SocialPageFactoryTest extends TestCase
{
    public function testSuccessGenerateFromPage(): void
    {
        $urlGenerator = $this->getContainer()->get('router');
        $socialPageFactory = new SocialPageFactory($urlGenerator);

        $pageUrl = $urlGenerator->generate('articles_list');

        $exceptedSocialImageUrl = $urlGenerator->generate('social_media_page_image', ['url' => $pageUrl]);

        $recordSocialPage = $socialPageFactory->createPageByUrl($pageUrl);

        $this->assertEquals($pageUrl, $recordSocialPage->getUrl());
        $this->assertEquals($exceptedSocialImageUrl, $recordSocialPage->getSocialImageUrl());
        $this->assertEmpty($recordSocialPage->getSourceImages());
    }
}
