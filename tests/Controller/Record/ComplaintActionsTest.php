<?php

namespace Tests\Controller\Record;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\News\Entity\News;
use App\Domain\User\Entity\User;
use Generator;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleWithManyComments;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\DataFixtures\ORM\Record\News\LoadNewsWithComments;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsWithRegion;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoByCompanyAuthor;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;

class ComplaintActionsTest extends TestCase
{
    private const ARTICLE_VIEW_PATH = '/articles/view/%d/';
    private const COMPLAIN_ACTION_NAME = 'Сообщить о нарушении';
    private const PART_OF_COMMENTS_SECTION_TITLE = 'омментари';

    private User $user;
    private Article $article;
    private News $news;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadArticleWithManyComments::class,
            LoadNewsWithComments::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($user instanceof User);
        $this->user = $user;

        $article = $referenceRepository->getReference(LoadArticleWithManyComments::REFERENCE_NAME);
        assert($article instanceof Article);
        $this->article = $article;

        $news = $referenceRepository->getReference(LoadNewsWithComments::REFERENCE_NAME);
        assert($news instanceof News);
        $this->news = $news;
    }

    public function testUserCanComplainOnArticleComment(): void
    {
        $browser = $this->getBrowser()->loginUser($this->user);
        $viewPage = $browser->request('GET', sprintf(self::ARTICLE_VIEW_PATH, $this->article->getId()));

        $link = $viewPage->filter('.commentsFS')->selectLink('Сообщить о нарушении')->link();
        $browser->click($link);

        $browser->submitForm('Сообщить', [
            'complaint[text]' => 'text',
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect(sprintf(
            '/articles/view/%d/#comment%s',
            $this->article->getId(),
            $this->article->getComments()->first()->getSlug()
        )));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Сообщение отправлено администрации сайта. Спасибо за вашу помощь!', $viewPage->html());
    }

    public function testUserCantComplainOnNews(): void
    {
        $browser = $this->getBrowser()->loginUser($this->user);
        $viewPage = $browser->request('GET', sprintf('/news/view/%d/', $this->news->getId()));

        $this->assertStringContainsString(self::PART_OF_COMMENTS_SECTION_TITLE, $viewPage->filter('.commentsFS__title')->html());
        $this->assertStringNotContainsString(self::COMPLAIN_ACTION_NAME, $viewPage->filter('.articleFS')->html());
    }

    public function testGuestCantComplainOnArticleComment(): void
    {
        $browser = $this->getBrowser();
        $viewPage = $browser->request('GET', sprintf(self::ARTICLE_VIEW_PATH, $this->article->getId()));

        $this->assertStringContainsString(self::PART_OF_COMMENTS_SECTION_TITLE, $viewPage->filter('.commentsFS__title')->html());
        $this->assertStringNotContainsString(self::COMPLAIN_ACTION_NAME, $viewPage->html());
    }

    /**
     * @dataProvider getRecordListViewUrlCases
     */
    public function testAdministratorsCantComplain(string $url): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadModeratorUser::class,
            LoadArticleWithManyComments::class,
            LoadNewsWithComments::class,
            LoadGallery::class,
            LoadTidingsWithRegion::class,
            LoadVideoByCompanyAuthor::class,
        ])->getReferenceRepository();

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);

        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);
        assert($moderator instanceof User);

        $users = [$admin, $moderator];

        foreach ($users as $user) {
            $browser = $this->getBrowser()->loginUser($user);
            $viewPage = $browser->request('GET', $url);
            $link = $viewPage->filter('a.articleFS__content__link')->link();
            $viewPage = $browser->click($link);

            $this->assertStringNotContainsString(self::COMPLAIN_ACTION_NAME, $viewPage->html());
        }
    }

    /**
     * @return Generator<array<string>>
     */
    public static function getRecordListViewUrlCases(): Generator
    {
        yield ['/articles/'];

        yield ['/tidings/'];

        yield ['/video/'];

        yield ['/gallery/'];

        yield ['/news/'];
    }
}
