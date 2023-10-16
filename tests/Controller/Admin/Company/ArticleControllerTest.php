<?php

namespace Tests\Controller\Admin\Company;

use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Entity\User;
use Generator;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleForSemanticLinks;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWithAuthor;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorAdvancedUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;

/**
 * @group admin-company-controller
 */
class ArticleControllerTest extends TestCase
{
    const COMPANY_ARTICLES_INDEX_PATH = '/admin/company-articles/';

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();
    }

    public function testAdminAccessAllowedIndex(): void
    {
        $browser = $this->getBrowser()->loginUser($this->loadAdmin());
        $viewPage = $browser->request('GET', self::COMPANY_ARTICLES_INDEX_PATH);

        $this->assertStringContainsString('Список записей компаний', $viewPage->html());
        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider getUserFixturesWhoDoNotHaveAccessToCompanyList
     */
    public function testAccessDeniedIndex(string $singleFixtureClass): void
    {
        $user = $this->loadFixture($singleFixtureClass, User::class);

        $browser = $this->getBrowser()->loginUser($user);
        $browser->request('GET', self::COMPANY_ARTICLES_INDEX_PATH);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAnonymousAccessDeniedForIndex(): void
    {
        $this->getBrowser()->request('GET', self::COMPANY_ARTICLES_INDEX_PATH);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testArticleSearchFilterByArticleTitle(): void
    {
        $admin = $this->loadAdmin();

        [$articleWithAuthor, $articleForSemanticLinks] = $this->loadCompanyArticles();

        $expectedArticleTitle = $articleWithAuthor->getTitle();
        $unexpectedArticleTitle = $articleForSemanticLinks->getTitle();

        $browser = $this->getBrowser()->loginUser($admin);
        $browser->request('GET', self::COMPANY_ARTICLES_INDEX_PATH);

        $viewPage = $browser->submitForm('Поиск', [
            'company_article_search[title]' => $expectedArticleTitle,
        ], 'GET');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString($expectedArticleTitle, $viewPage->filter('tbody')->html());
        $this->assertStringNotContainsString($unexpectedArticleTitle, $viewPage->filter('tbody')->html());

        $resetLink = $viewPage->selectLink('Сбросить')->link();
        $viewPage = $browser->click($resetLink);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $expectedArticleTitles = [$articleWithAuthor->getTitle(), $articleForSemanticLinks->getTitle()];
        foreach ($expectedArticleTitles as $expectedArticleTitle) {
            $this->assertStringContainsString($expectedArticleTitle, $viewPage->filter('tbody')->html());
        }
    }

    public function getUserFixturesWhoDoNotHaveAccessToCompanyList(): Generator
    {
        yield [LoadModeratorUser::class];

        yield [LoadUserWithoutRecords::class];

        yield [LoadModeratorAdvancedUser::class];
    }

    private function loadAdmin(): User
    {
        return $this->loadFixture(LoadAdminUser::class, User::class);
    }

    private function loadCompanyArticles(): array
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyArticleWithAuthor::class,
            LoadCompanyArticleForSemanticLinks::class,
        ], true)->getReferenceRepository();

        $companyArticleWithAuthor = $referenceRepository->getReference(LoadCompanyArticleWithAuthor::getReferenceName());
        assert($companyArticleWithAuthor instanceof CompanyArticle);

        $companyArticleForSemanticLinks = $referenceRepository->getReference(LoadCompanyArticleForSemanticLinks::getReferenceName());
        assert($companyArticleForSemanticLinks instanceof CompanyArticle);

        return [$companyArticleWithAuthor, $companyArticleForSemanticLinks];
    }
}
