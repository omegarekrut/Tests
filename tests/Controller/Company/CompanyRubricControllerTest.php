<?php

namespace Tests\Controller\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Rubric;
use Tests\Controller\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\Company\LoadManySimpleOwnedCompanies;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;

/**
 * @group company
 */
class CompanyRubricControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();
        $this->browser = $this->getBrowser();
    }

    public function testSeeCompanyByRubricPage(): void
    {
        $rubricWithCompany = $this->loadFixture(LoadDefaultRubric::class, Rubric::class);;
        $companiesRubricPage = $this->getRubricPageUrl($rubricWithCompany->getSlug());

        $pageContent = $this->browser->request('GET', $companiesRubricPage);

        $this->assertEquals(Response::HTTP_OK, $this->browser->getResponse()->getStatusCode());
        $this->assertStringContainsString($rubricWithCompany->getName(), $pageContent->filter('h1')->text());
    }

    public function testSeeCompany(): void
    {
        $company = $this->loadFixture(LoadCompanyWithOwner::class, Company::class);
        $companiesRubricPage = $this->getRubricPageUrl($company->getRubrics()->first()->getSlug());

        $crawler = $this->browser->request('GET', $companiesRubricPage);
        $link = $crawler->filter('a.company-list-item__name')->link();

        $this->browser->click($link);

        $this->assertEquals(Response::HTTP_OK, $this->browser->getResponse()->getStatusCode());
    }

    public function testPagination(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadManySimpleOwnedCompanies::class,
        ])->getReferenceRepository();
        $oneCompanyReferenceName = LoadManySimpleOwnedCompanies::getRandomReferenceName();

        $oneCompany = $referenceRepository->getReference($oneCompanyReferenceName);
        assert($oneCompany instanceof Company);

        $tackleShopsRubric = $oneCompany->getRubrics()->first();
        assert($tackleShopsRubric instanceof Rubric);

        $companiesRubricPage = $this->getRubricPageUrl($tackleShopsRubric->getSlug());
        $rubricRuName = $tackleShopsRubric->getName();

        $crawler = $this->browser->request('GET', $companiesRubricPage);
        $link = $crawler->filter('.pagination .arrow--next a')->link();

        $this->browser->click($link);

        $textH1 = sprintf('%s. Страница 2', $rubricRuName);

        $this->assertEquals(Response::HTTP_OK, $this->browser->getResponse()->getStatusCode());
        $this->assertStringContainsString($crawler->filter('h1')->text(), $textH1);
        $this->assertNotNull($crawler->selectLink('Предыдущая'));
    }

    public function testDontSeeCompanyByRubricFirstPage(): void
    {
        $company = $this->loadFixture(LoadCompanyWithOwner::class, Company::class);
        $companiesRubricPage = $this->getRubricPageUrl($company->getRubrics()->first()->getSlug());

        $this->browser->request('GET', $companiesRubricPage . 'page1/');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->browser->getResponse()->getStatusCode());
    }

    private function getRubricPageUrl(string $slug): string
    {
        return sprintf('/companies/rubric/%s/', $slug);
    }
}
