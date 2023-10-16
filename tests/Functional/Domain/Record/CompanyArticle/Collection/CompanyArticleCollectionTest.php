<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\Collection;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\CompanyArticle\Collection\CompanyArticleCollection;
use App\Domain\Record\CompanyArticle\Repository\CompanyArticleRepository;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWherePublishedLater;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadManyCompanyArticles;
use Tests\Functional\TestCase;

class CompanyArticleCollectionTest extends TestCase
{
    public function testSortByCreatedAtAsc(): void
    {
        $this->loadFixtures([
            LoadManyCompanyArticles::class,
        ]);

        $companyArticles = [];

        $companyArticleRepository = $this->getContainer()->get(CompanyArticleRepository::class);

        $companyArticleIds = $companyArticleRepository->getAllCompanyArticleIds();

        foreach ($companyArticleIds as $articleId) {
            $companyArticles[] = $companyArticleRepository->find($articleId);
        }

        $unsortedCompanyArticles = new CompanyArticleCollection($companyArticles);

        $sortedCompanyArticles = $unsortedCompanyArticles->sortByCreatedAtAsc();

        for ($i = 0; $i < $sortedCompanyArticles->count() - 1; $i++) {
            /** @var Record $leftCompanyArticle */
            $leftCompanyArticle = $sortedCompanyArticles->get($i);
            /** @var Record $rightCompanyArticle */
            $rightCompanyArticle = $sortedCompanyArticles->get($i + 1);

            $this->assertTrue($leftCompanyArticle->getCreatedAt() <= $rightCompanyArticle->getCreatedAt());
        }
    }

    public function testArticlesArePublished(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompanyArticle::class,
        ])->getReferenceRepository();

        $publishedArticle = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompanyArticle::REFERENCE_NAME);

        $publishedArticleCollection = new CompanyArticleCollection([$publishedArticle]);

        $this->assertContains($publishedArticle, $publishedArticleCollection->filterArticlesArePublished());
    }

    public function testArticlesAreNotPublished(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyArticleWherePublishedLater::class,
        ])->getReferenceRepository();

        $notPublishedArticle = $referenceRepository->getReference(LoadCompanyArticleWherePublishedLater::REFERENCE_NAME);

        $notPublishedArticleCollection = new CompanyArticleCollection([$notPublishedArticle]);

        $this->assertEmpty($notPublishedArticleCollection->filterArticlesArePublished());
    }
}
