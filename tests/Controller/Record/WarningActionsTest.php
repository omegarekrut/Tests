<?php

namespace Tests\Controller\Record;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\News\Entity\News;
use App\Domain\User\Entity\User;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleWithManyComments;
use Tests\DataFixtures\ORM\Record\LoadNewsWithHashtagInText;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;

class WarningActionsTest extends TestCase
{
    private const ARTICLE_VIEW_PATH = '/articles/view/%d/';
    private const WARN_ACTION_NAME = 'Предупредить';
    private const PART_OF_COMMENTS_SECTION_TITLE = 'омментари';

    private User $admin;
    private User $user;
    private Article $article;
    private News $news;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadUserWithAvatar::class,
            LoadArticleWithManyComments::class,
            LoadNewsWithHashtagInText::class,
        ])->getReferenceRepository();

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);
        $this->admin = $admin;

        $user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($user instanceof User);
        $this->user = $user;

        $article = $referenceRepository->getReference(LoadArticleWithManyComments::REFERENCE_NAME);
        assert($article instanceof Article);
        $this->article = $article;

        $news = $referenceRepository->getReference(LoadNewsWithHashtagInText::REFERENCE_NAME);
        assert($news instanceof News);
        $this->news = $news;
    }

    public function testAdminCanWarnOnArticle(): void
    {
        $browser = $this->getBrowser()->loginUser($this->admin);
        $viewPage = $browser->request('GET', sprintf(self::ARTICLE_VIEW_PATH, $this->article->getId()));

        $this->assertStringContainsString(self::WARN_ACTION_NAME, $viewPage->filter('.articleFS')->html());

        $link = $viewPage->filter('.articleFS')->selectLink(self::WARN_ACTION_NAME)->link();
        $viewPage = $browser->click($link);

        $this->assertStringContainsString('Вынести предупреждение', $viewPage->html());

        $browser->submitForm(self::WARN_ACTION_NAME);

        $this->assertTrue($browser->getResponse()->isRedirect(sprintf(self::ARTICLE_VIEW_PATH, $this->article->getId())));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Предупреждение отправлено.', $viewPage->html());
    }

    public function testAdminCantWarnOnNews(): void
    {
        $browser = $this->getBrowser()->loginUser($this->admin);
        $viewPage = $browser->request('GET', sprintf('/news/view/%d/', $this->news->getId()));

        $this->assertStringNotContainsString(self::WARN_ACTION_NAME, $viewPage->filter('.articleFS')->html());
    }

    public function testUserCantWarnOnArticle(): void
    {
        $browser = $this->getBrowser()->loginUser($this->user);
        $viewPage = $browser->request('GET', sprintf(self::ARTICLE_VIEW_PATH, $this->article->getId()));

        $this->assertStringContainsString(self::PART_OF_COMMENTS_SECTION_TITLE, $viewPage->filter('.commentsFS__title')->html());
        $this->assertStringNotContainsString(self::WARN_ACTION_NAME, $viewPage->filter('.articleFS')->html());
    }

    public function testGuestCantWarnOnArticle(): void
    {
        $browser = $this->getBrowser();
        $viewPage = $browser->request('GET', sprintf(self::ARTICLE_VIEW_PATH, $this->article->getId()));

        $this->assertStringContainsString(self::PART_OF_COMMENTS_SECTION_TITLE, $viewPage->filter('.commentsFS__title')->html());
        $this->assertStringNotContainsString(self::WARN_ACTION_NAME, $viewPage->filter('.articleFS')->html());
    }
}
