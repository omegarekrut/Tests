<?php

namespace Tests\Controller\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Entity\User;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;

class CompanyArticleAccessCrudControllerTest extends TestCase
{
    private const COMPANY_ARTICLE_CREATE_TITLE = 'Добавить запись компании';
    private const COMPANY_ARTICLE_EDIT_TITLE = 'Редактировать запись компании';
    private const ACCESS_DENIED = 'Access Denied';
    private const COMPANY_ARTICLE_CREATE_BUTTON = 'Добавить запись';

    private User $admin;
    private User $moderator;
    private User $user;
    private User $companyOwner;
    private CompanyArticle $companyArticle;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadModeratorUser::class,
            LoadMostActiveUser::class,
            LoadAquaMotorcycleShopsCompanyArticle::class,
        ])->getReferenceRepository();

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);

        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);
        assert($moderator instanceof User);

        $user = $referenceRepository->getReference(LoadMostActiveUser::USER_MOST_ACTIVE);
        assert($user instanceof User);

        $companyArticle = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompanyArticle::REFERENCE_NAME);
        assert($companyArticle instanceof CompanyArticle);

        $company = $companyArticle->getCompanyAuthor();
        $companyOwner = $company->getOwner();

        $this->admin = $admin;
        $this->moderator = $moderator;
        $this->user = $user;
        $this->companyOwner = $companyOwner;
        $this->companyArticle = $companyArticle;
        $this->company = $company;
    }

    public function testAllowedCreateCompanyArticleForCompanyOwner(): void
    {
        $page = $this->createCrawlerForCompanyArticleCreatePage($this->companyOwner, $this->company);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::COMPANY_ARTICLE_CREATE_TITLE,
            $page->html()
        );
    }

    public function testAllowedCreateCompanyArticleForAdmin(): void
    {
        $page = $this->createCrawlerForCompanyArticleCreatePage($this->admin, $this->company);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::COMPANY_ARTICLE_CREATE_TITLE,
            $page->html()
        );
    }

    public function testAllowedEditCompanyArticleForCompanyOwner(): void
    {
        $page = $this->createCrawlerForCompanyArticleEditPage(
            $this->companyArticle->getCompanyAuthor()->getOwner(),
            $this->companyArticle
        );

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::COMPANY_ARTICLE_EDIT_TITLE,
            $page->html()
        );
    }

    public function testAllowedEditCompanyArticleForArticleAuthor(): void
    {
        $page = $this->createCrawlerForCompanyArticleEditPage(
            $this->companyArticle->getAuthor(),
            $this->companyArticle
        );

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::COMPANY_ARTICLE_EDIT_TITLE,
            $page->html()
        );
    }

    public function testDeniedCreateCompanyArticleForAuthorizedUserNotCompanyOwner(): void
    {
        $page = $this->createCrawlerForCompanyArticleCreatePage($this->user, $this->company);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::ACCESS_DENIED,
            $page->html()
        );
    }

    public function testAllowedCreateCompanyArticleForModeratorUserNotCompanyOwner(): void
    {
        $page = $this->createCrawlerForCompanyArticleCreatePage($this->moderator, $this->company);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::COMPANY_ARTICLE_CREATE_TITLE,
            $page->html()
        );
    }

    public function testDeniedEditCompanyArticleForAuthorizedUserNotCompanyOwner(): void
    {
        $page = $this->createCrawlerForCompanyArticleEditPage(
            $this->user,
            $this->companyArticle
        );

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::ACCESS_DENIED,
            $page->html()
        );
    }

    public function testAllowedEditCompanyArticleForModeratorUserNotCompanyOwner(): void
    {
        $page = $this->createCrawlerForCompanyArticleEditPage(
            $this->moderator,
            $this->companyArticle
        );

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::COMPANY_ARTICLE_EDIT_TITLE,
            $page->html()
        );
    }

    public function testSeeCreateCompanyArticleButtonForCompanyOwner(): void
    {
        $page = $this->createCrawlerForCompanyPage($this->companyOwner, $this->company);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::COMPANY_ARTICLE_CREATE_BUTTON,
            $page->html()
        );
    }

    public function testSeeCreateCompanyArticleButtonForAdmin(): void
    {
        $page = $this->createCrawlerForCompanyPage($this->admin, $this->company);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::COMPANY_ARTICLE_CREATE_BUTTON,
            $page->html()
        );
    }

    public function testNotSeeCreateCompanyArticleButtonForAuthorizedUserNotOwnerCompany(): void
    {
        $page = $this->createCrawlerForCompanyPage($this->user, $this->company);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringNotContainsString(
            self::COMPANY_ARTICLE_CREATE_BUTTON,
            $page->html()
        );
    }

    private function createCrawlerForCompanyArticleCreatePage(User $user, Company $company): Crawler
    {
        $client = $this->getBrowser()->loginUser($user);
        $url = sprintf(
            '/company-articles/%s/%s/create/',
            $company->getSlug(),
            $company->getShortUuid()
        );

        return $client->request('GET', $url);
    }

    private function createCrawlerForCompanyArticleEditPage(User $user, CompanyArticle $companyArticle): Crawler
    {
        $client = $this->getBrowser()->loginUser($user);
        $url = sprintf(
            '/company-articles/%s/edit/',
            $companyArticle->getId(),
        );

        return $client->request('GET', $url);
    }

    private function createCrawlerForCompanyPage(User $user, Company $company): Crawler
    {
        $client = $this->getBrowser()->loginUser($user);
        $url = sprintf(
            '/companies/%s/%s/',
            $company->getSlug(),
            $company->getShortUuid(),
        );

        return $client->request('GET', $url);
    }
}
