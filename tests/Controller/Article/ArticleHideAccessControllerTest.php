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

class ArticleHideAccessControllerTest extends TestCase
{
    private const ARTICLE_HIDE_MESSAGE = 'Запись успешно удалена.';

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

    public function testAdminCanHideArticleByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->admin);

        $browser->request('GET', sprintf('/articles/%s/hide/', $this->articleByCompanyAuthor->getId()));

        $this->assertTrue($browser->getResponse()->isRedirect('/articles/'));

        $page = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', self::ARTICLE_HIDE_MESSAGE, $page->html());
    }

    public function testModeratorCanHideArticleByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->moderator);

        $browser->request('GET', sprintf('/articles/%s/hide/', $this->articleByCompanyAuthor->getId()));

        $this->assertTrue($browser->getResponse()->isRedirect('/articles/'));

        $page = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', self::ARTICLE_HIDE_MESSAGE, $page->html());
    }

    public function testCompanyEmployeeCannotHideArticleByCompanyAuthor()
    {
        $companyEmployee = $this->articleByCompanyAuthor->getCompanyAuthor()->getEmployees()->getEmployeesAsUsers()->first();

        $browser = $this->getBrowser()
            ->loginUser($companyEmployee);

        $browser->request('GET', sprintf('/articles/%s/hide/', $this->articleByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testCompanyOwnerCannotHideArticleByCompanyAuthor()
    {
        $companyOwner = $this->articleByCompanyAuthor->getCompanyAuthor()->getOwner();

        $browser = $this->getBrowser()
            ->loginUser($companyOwner);

        $browser->request('GET', sprintf('/articles/%s/hide/', $this->articleByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAuthorCannotHideArticle()
    {
        $author = $this->articleByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($author);

        $browser->request('GET', sprintf('/articles/%s/hide/', $this->articleByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testUserCannotHideArticle()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->user);

        $browser->request('GET', sprintf('/articles/%s/hide/', $this->articleByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testGuestCannotHideArticle()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/articles/%s/hide/', $this->articleByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
