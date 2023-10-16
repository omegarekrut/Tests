<?php

namespace Tests\Functional\Domain\SocialMediaImageMaker;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\SocialMediaImageMaker\SocialMediaImageUrlGenerator;
use Symfony\Component\Routing\Router;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\TestCase;

class SocialMediaImageUrlGeneratorTest extends TestCase
{
    private $urlGenerator;
    private $socialMediaImageUrlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        /* @var Router urlGenerator */
        $this->urlGenerator = $this->getContainer()->get('router');

        /* @var SocialMediaImageUrlGenerator socialMediaImageUrlGenerator */
        $this->socialMediaImageUrlGenerator = $this->getContainer()->get(SocialMediaImageUrlGenerator::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->urlGenerator,
            $this->socialMediaImageUrlGenerator
        );

        parent::tearDown();
    }

    public function testSuccessGenerateFromRecordPage(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
        ])->getReferenceRepository();

        /** @var Article $article */
        $article = $referenceRepository->getReference(LoadArticles::getRandReferenceName());

        $parametersUri = [
            'articles',
            'view',
            $article->getId(),
        ];

        $exceptedUrl = $this->urlGenerator->generate('social_media_record_page_image', ['record' => $article->getId()], $this->urlGenerator::ABSOLUTE_URL);

        $actualUrl = $this->socialMediaImageUrlGenerator->generateFromParametersUri('', $parametersUri);

        $this->assertEquals($exceptedUrl, $actualUrl);
    }

    public function testSuccessGenerateFromAnotherPage(): void
    {
        $pageUrl = $this->urlGenerator->generate('articles_list', [], $this->urlGenerator::ABSOLUTE_URL);

        $parametersUri = [
            'articles',
        ];

        $exceptedUrl = $this->urlGenerator->generate('social_media_page_image', ['url' => $pageUrl], $this->urlGenerator::ABSOLUTE_URL);

        $actualUrl = $this->socialMediaImageUrlGenerator->generateFromParametersUri($pageUrl, $parametersUri);

        $this->assertEquals($exceptedUrl, $actualUrl);
    }
}
