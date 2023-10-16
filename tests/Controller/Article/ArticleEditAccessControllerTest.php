<?php

namespace Tests\Controller\Article;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleByCompanyAuthor;
use Tests\DataFixtures\ORM\Record\Articles\LoadSimpleArticle;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;

class ArticleEditAccessControllerTest extends TestCase
{
    private const ARTICLE_EDIT_TITLE = 'Редактировать запись';

    private Article $articleByCompanyAuthor;
    private Article $articleByAuthor;
    private User $admin;
    private User $moderator;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticleByCompanyAuthor::class,
            LoadSimpleArticle::class,
            LoadAdminUser::class,
            LoadModeratorUser::class,
            LoadUserWithoutRecords::class,
        ])->getReferenceRepository();

        $articleByCompanyAuthor = $referenceRepository->getReference(LoadArticleByCompanyAuthor::REFERENCE_NAME);
        assert($articleByCompanyAuthor instanceof Article);
        $this->articleByCompanyAuthor = $articleByCompanyAuthor;

        $articleByAuthor = $referenceRepository->getReference(LoadSimpleArticle::REFERENCE_NAME);
        assert($articleByAuthor instanceof Article);
        $this->articleByAuthor = $articleByAuthor;

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);
        $this->admin = $admin;

        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);
        assert($moderator instanceof User);
        $this->moderator = $moderator;

        $user = $referenceRepository->getReference(LoadUserWithoutRecords::REFERENCE_NAME);
        assert($user instanceof User);
        $this->user = $user;
    }

    public function testCompanyEmployeeCanEditArticleByCompanyAuthor()
    {
        $companyEmployee = $this->articleByCompanyAuthor->getCompanyAuthor()->getEmployees()->getEmployeesAsUsers()->first();

        $browser = $this->getBrowser()
            ->loginUser($companyEmployee);

        $page = $browser->request('GET', sprintf('/articles/%s/edit/', $this->articleByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::ARTICLE_EDIT_TITLE, $page->text());
    }

    public function testCompanyOwnerCanEditArticleByCompanyAuthor()
    {
        $companyOwner = $this->articleByCompanyAuthor->getCompanyAuthor()->getOwner();

        $browser = $this->getBrowser()
            ->loginUser($companyOwner);

        $page = $browser->request('GET', sprintf('/articles/%s/edit/', $this->articleByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::ARTICLE_EDIT_TITLE, $page->text());
    }

    public function testAdminCanEditArticleByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->admin);

        $page = $browser->request('GET', sprintf('/articles/%s/edit/', $this->articleByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::ARTICLE_EDIT_TITLE, $page->text());
    }

    public function testModeratorCanEditArticleByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->moderator);

        $page = $browser->request('GET', sprintf('/articles/%s/edit/', $this->articleByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::ARTICLE_EDIT_TITLE, $page->text());
    }

    public function testAuthorCanEditArticle()
    {
        $author = $this->articleByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($author);

        $page = $browser->request('GET', sprintf('/articles/%s/edit/', $this->articleByAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::ARTICLE_EDIT_TITLE, $page->text());
    }

    public function testUserCannotEditArticle()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->user);

        $browser->request('GET', sprintf('/articles/%s/edit/', $this->articleByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testGuestCannotEditArticle()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/articles/%s/edit/', $this->articleByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
