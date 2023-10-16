<?php

namespace Tests\Functional\Module\SocialMediaImageMaker;

use App\Module\SocialMediaImageMaker\ImageMaking\ImagePreparer;
use App\Module\SocialMediaImageMaker\Model\SocialImage;
use App\Module\SocialMediaImageMaker\Model\SourceImage;
use App\Module\SocialMediaImageMaker\Resolver\PageTitleResolver;
use App\Module\SocialMediaImageMaker\SocialImageMaker;
use App\Module\SocialMediaImageMaker\SocialPage;
use Tests\Functional\TestCase;

class SocialImageMakerTest extends TestCase
{
    private $socialImageMaker;
    private $defaultImageUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $kernelRootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $this->defaultImageUrl = sprintf('file://%s/../www/img/og/fishingsib.jpg', $kernelRootDir);
        $pageTitleResolver = $this->createMock(PageTitleResolver::class);
        $imagePreparer = $this->getContainer()->get(ImagePreparer::class);

        $this->socialImageMaker = new SocialImageMaker($pageTitleResolver, $imagePreparer, $this->defaultImageUrl);
    }

    protected function tearDown(): void
    {
        unset(
            $this->socialImageMaker,
            $this->defaultImageUrl
        );

        parent::tearDown();
    }

    public function testMakeSocialImageFromRecord(): void
    {
        $recordSocialPage = new SocialPage('article', '', [new SourceImage($this->defaultImageUrl, 968, 504)]);

        $createdSocialImage = $this->socialImageMaker->makeImage($recordSocialPage);

        $this->assertInstanceOf(SocialImage::class, $createdSocialImage);
        $this->assertNotNull($createdSocialImage->mime);
        $this->assertNotNull($createdSocialImage->content);
    }

    public function testMakeSocialImageFromPage(): void
    {
        $recordSocialPage = new SocialPage('http://example.mock/default', '', []);

        $createdSocialImage = $this->socialImageMaker->makeImage($recordSocialPage);

        $this->assertInstanceOf(SocialImage::class, $createdSocialImage);
        $this->assertNotNull($createdSocialImage->mime);
        $this->assertNotNull($createdSocialImage->content);
    }
}
