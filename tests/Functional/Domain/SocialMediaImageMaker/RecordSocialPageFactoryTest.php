<?php

namespace Tests\Functional\Domain\SocialMediaImageMaker;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\SocialMediaImageMaker\RecordSocialPageFactory;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformerFactory;
use ImageApi\Client as ClientApi;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoWithoutImage;
use Tests\Functional\TestCase;

class RecordSocialPageFactoryTest extends TestCase
{
    /** @var UrlGeneratorInterface  */
    private $urlGenerator;
    /** @var RecordViewUrlGenerator */
    private $recordViewUrlGenerator;
    /** @var RecordSocialPageFactory  */
    private $recordSocialPageFactory;
    private $defaultImageUrl;
    /** @var Article */
    private $article;
    /** @var Video */
    private $videoWithoutImage;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadVideoWithoutImage::class,
        ])->getReferenceRepository();

        $this->article = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->videoWithoutImage = $referenceRepository->getReference(LoadVideoWithoutImage::REFERENCE_NAME);

        $this->defaultImageUrl = $this->getContainer()->getParameter('social_background_image_url');
        $clientApiMock = $this->createClientApiMock($this->article->getImages());
        $this->urlGenerator = $this->getContainer()->get('router');
        $this->recordViewUrlGenerator = $this->getContainer()->get(RecordViewUrlGenerator::class);
        $imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);

        $this->recordSocialPageFactory = new RecordSocialPageFactory(
            $this->urlGenerator,
            $this->recordViewUrlGenerator,
            $imageTransformerFactory,
            $clientApiMock
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->urlGenerator,
            $this->recordViewUrlGenerator,
            $this->recordSocialPageFactory,
            $this->defaultImageUrl,
            $this->article,
            $this->videoWithoutImage
        );

        parent::tearDown();
    }

    public function testSuccessGenerateFromRecordPage(): void
    {
        $exceptedUrl = $this->recordViewUrlGenerator->generate($this->article);
        $exceptedSocialImageUrl = $this->urlGenerator->generate('social_media_record_page_image', ['record' => $this->article->getId()]);

        $recordSocialPage = $this->recordSocialPageFactory->createPageByRecord($this->article);

        $this->assertEquals($exceptedUrl, $recordSocialPage->getUrl());
        $this->assertEquals($exceptedSocialImageUrl, $recordSocialPage->getSocialImageUrl());
        $this->assertNotEmpty($recordSocialPage->getSourceImages());
    }

    public function testSuccessGenerateFromRecordPageWhenRecordNotHaveImage(): void
    {
        $exceptedUrl = $this->recordViewUrlGenerator->generate($this->videoWithoutImage);
        $exceptedSocialImageUrl = $this->urlGenerator->generate('social_media_record_page_image', ['record' => $this->videoWithoutImage->getId()]);

        $recordSocialPage = $this->recordSocialPageFactory->createPageByRecord($this->videoWithoutImage);

        $this->assertEquals($exceptedUrl, $recordSocialPage->getUrl());
        $this->assertEquals($exceptedSocialImageUrl, $recordSocialPage->getSocialImageUrl());
        $this->assertEmpty($recordSocialPage->getSourceImages());
    }

    /**
     * @param Image[] $images
     */
    private function createClientApiMock(iterable $images): ClientApi
    {
        $sizesDefaultImage = [];

        foreach ($images as $image) {
            $sizesDefaultImage[$image->getFilename()] = ['width' => 650, 'height' => 350];
        }

        $clientApiMock = $this->createMock(ClientApi::class);
        $clientApiMock
            ->method('getSize')
            ->willReturn($sizesDefaultImage);

        return $clientApiMock;
    }
}
