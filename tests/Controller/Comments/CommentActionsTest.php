<?php

namespace Tests\Controller\Comments;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\User\Entity\User;
use App\Util\FrameworkBundle\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Generator;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleWithComment;
use Tests\DataFixtures\ORM\Record\Gallery\LoadGalleryWithComment;
use Tests\DataFixtures\ORM\Record\News\LoadNewsWithComment;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsWithComment;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoWithComment;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;

class CommentActionsTest extends TestCase
{
    private RecordViewUrlGenerator $recordViewUrlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();

        $this->recordViewUrlGenerator = $this->getContainer()->get(RecordViewUrlGenerator::class);
    }

    /**
     * @dataProvider gedRecordFixturesWithComments
     */
    public function testGuestCantAddComment(string $recordFixtureClass): void
    {
        $record = $this->loadFixture($recordFixtureClass, Record::class);
        $viewPage = $this->getRecordViewPage($this->getBrowser(), $record);

        $this->assertStringContainsString('Войдите на сайт, чтобы оставлять комментарии.', $viewPage->html());
    }

    /**
     * @dataProvider gedRecordFixturesWithComments
     */
    public function testUserCanAddComment(string $recordFixtureClass): void
    {
        $user = $this->loadFixture(LoadUserWithAvatar::class, User::class);

        $record = $this->loadFixture($recordFixtureClass, Record::class);

        $browser = $this->getBrowser()->loginUser($user);
        $this->getRecordViewPage($browser, $record);

        $browser->submitForm('Написать', [
            'create_comment[text]' => 'test comment',
        ]);

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Ваш комментарий успешно добавлен.', $viewPage->html());
    }

    /**
     * @dataProvider gedRecordFixturesWithComments
     */
    public function testUserCanEditOwnComment(string $recordFixtureClass): void
    {
        $record = $this->loadFixture($recordFixtureClass, Record::class);

        $comment = $record->getComments()->first();
        assert($comment instanceof Comment);

        $user = $comment->getAuthor();
        assert($user instanceof User);

        $browser = $this->getBrowser()->loginUser($user);
        $viewPage = $this->getRecordViewPage($browser, $record);

        $commentContainer = self::getCommentContainer($viewPage);
        $browser->click(self::getEditCommentButton($commentContainer));

        $browser->submitForm('Сохранить');

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Комментарий успешно изменен.', $viewPage->html());
    }

    /**
     * @dataProvider gedRecordFixturesWithComments
     */
    public function testAdminCanEditRecordComment(string $recordFixtureClass): void
    {
        $admin = $this->loadAdmin();

        $record = $this->loadFixture($recordFixtureClass, Record::class);

        $browser = $this->getBrowser()->loginUser($admin);
        $viewPage = $this->getRecordViewPage($browser, $record);

        $commentContainer = $this->getCommentContainer($viewPage);
        $browser->click(self::getEditCommentButton($commentContainer));

        $browser->submitForm('Сохранить');

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Комментарий успешно изменен.', $viewPage->html());
    }

    /**
     * @dataProvider gedRecordFixturesWithComments
     */
    public function testAdminCanHideAndRestoreRecordComment(string $recordFixtureClass): void
    {
        $admin = $this->loadAdmin();

        $record = $this->loadFixture($recordFixtureClass, Record::class);

        $browser = $this->getBrowser()->loginUser($admin);
        $viewPage = $this->getRecordViewPage($browser, $record);

        $commentContainer = self::getCommentContainer($viewPage);
        $commentText = self::getCommentText($commentContainer);

        $browser->click(self::getHideCommentButton($commentContainer));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Комментарий скрыт', $viewPage->html());
        $this->assertStringContainsString($commentText, $viewPage->filter('.commentsFS__item--deleted .commentsFS__text')->html());

        $browser->click(self::getRestoreCommentButton($viewPage));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Комментарий восстановлен', $viewPage->html());
    }

    /**
     * @dataProvider gedRecordFixturesWithComments
     */
    public function testRecordAuthorCanHideAndRestoreComment(string $recordFixtureClass): void
    {
        $record = $this->loadFixture($recordFixtureClass, Record::class);
        $recordAuthor = $record->getAuthor();
        assert($recordAuthor instanceof User);

        $browser = $this->getBrowser()->loginUser($recordAuthor);
        $viewPage = $this->getRecordViewPage($browser, $record);

        $commentContainer = self::getCommentContainer($viewPage);
        $commentText = self::getCommentText($commentContainer);

        $browser->click(self::getHideCommentButton($commentContainer));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Комментарий скрыт', $viewPage->html());
        $this->assertStringContainsString($commentText, $viewPage->filter('.commentsFS__item--deleted .commentsFS__text')->html());

        $browser->click(self::getRestoreCommentButton($viewPage));

        $viewPage = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', 'Комментарий восстановлен', $viewPage->html());
    }

    /**
     * @dataProvider gedRecordFixturesWithComments
     */
    public function testAdminCanDeleteRecordComment(string $recordFixtureClass): void
    {
        $admin = $this->loadAdmin();

        $record = $this->loadFixture($recordFixtureClass, Record::class);

        $browser = $this->getBrowser()->loginUser($admin);
        $viewPage = $this->getRecordViewPage($browser, $record);

        $commentContainer = self::getCommentContainer($viewPage);
        $commentText = self::getCommentText($commentContainer);

        $browser->click(self::getHideCommentButton($commentContainer));

        $viewPage = $browser->followRedirect();

        $browser->click(self::getDeleteCommentButton($viewPage));

        $viewPage = $browser->followRedirect();

        $this->assertStringNotContainsString($commentText, $viewPage->html());
        $this->assertSeeAlertInPageContent('success', 'Комментарий удален', $viewPage->html());
    }

    /**
     * @return Generator<array{0: class-string<Fixture>, 1: string}>
     */
    public static function gedRecordFixturesWithComments(): Generator
    {
        yield [LoadArticleWithComment::class];

        yield [LoadNewsWithComment::class];

        yield [LoadVideoWithComment::class];

        yield [LoadTidingsWithComment::class];

        yield [LoadGalleryWithComment::class];

    }

    private function loadAdmin(): User
    {
        return $this->loadFixture(LoadAdminUser::class, User::class);
    }

    private function getRecordViewPage(Client $browser, Record $record): Crawler
    {
        $viewRecordPageUrl = $this->recordViewUrlGenerator->generate($record);

        return $browser->request('GET', $viewRecordPageUrl);
    }

    private static function getCommentContainer(Crawler $viewPage): Crawler
    {
        return $viewPage->filter('.default-comments')->first();
    }

    private static function getCommentText(Crawler $commentContainer): string
    {
        return $commentContainer->filter('.commentsFS__item--active .commentsFS__text')->text();
    }

    private static function getEditCommentButton(Crawler $commentContainer): Link
    {
        return $commentContainer->selectLink('Редактировать')->link();
    }

    private static function getHideCommentButton(Crawler $commentContainer): Link
    {
        return $commentContainer->selectLink('Скрыть комментарий')->link();
    }

    private static function getRestoreCommentButton(Crawler $viewPage): Link
    {
        return $viewPage->filter('.commentsFS')->selectLink('Восстановить')->link();
    }

    private static function getDeleteCommentButton(Crawler $viewPage): Link
    {
        return $viewPage->filter('.commentsFS')->selectLink('Удалить')->link();
    }
}
