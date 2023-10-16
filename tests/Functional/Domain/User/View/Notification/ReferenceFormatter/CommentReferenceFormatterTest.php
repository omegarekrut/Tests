<?php

namespace Tests\Functional\Domain\User\View\Notification\ReferenceFormatter;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\View\Notification\ConcreteNotificationViewFactory\ReferenceFormatter\CommentReferenceFormatter;
use App\Twig\Comment\CommentTextViewFilter;
use App\Twig\User\ConvertMentionToLink;
use App\Util\StringFilter\SubSentenceFilter;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class CommentReferenceFormatterTest extends TestCase
{
    /** @var CommentReferenceFormatter */
    private $commentReferenceFormatter;
    /** @var ConvertMentionToLink */
    private $convertMentionToLink;
    /** @var Record */
    private $record;
    /** @var UrlGeneratorInterface */
    private $urlGenerator;
    /** @var Comment */
    private $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
        ])->getReferenceRepository();

        $this->commentReferenceFormatter = $this->getContainer()->get(CommentReferenceFormatter::class);
        $this->convertMentionToLink = $this->getContainer()->get(ConvertMentionToLink::class);
        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->comment = $this->record->getComments()->first();
        $this->urlGenerator = $this->getContainer()->get('router');
    }

    protected function tearDown(): void
    {
        unset(
            $this->commentReferenceFormatter,
            $this->convertMentionToLink,
            $this->record,
            $this->comment,
            $this->urlGenerator
        );

        parent::tearDown();
    }

    public function testReferenceShouldContainsShortCommentText(): void
    {
        $commentTextViewFilter = new CommentTextViewFilter($this->convertMentionToLink);
        $subSentenceFilter = new SubSentenceFilter();

        $commentText = $commentTextViewFilter($this->comment->getText());
        $expectedShortText = $subSentenceFilter($commentText, 50);

        $reference = $this->commentReferenceFormatter->formatReference($this->comment);

        $this->assertStringContainsString($expectedShortText, $reference);
    }

    public function testReferenceMustPointToRecordWithCommentAnchor(): void
    {
        $expectedReferenceUrl = $this->generateCommentUrl($this->comment);

        $reference = $this->commentReferenceFormatter->formatReference($this->comment);

        $this->assertStringContainsString($expectedReferenceUrl, $reference);
    }

    public function testReferenceToSomeCommentsShouldContainsRecordTitle(): void
    {
        $expectedTitle = $this->record->getTitle();

        $reference = $this->commentReferenceFormatter->formatReferences($this->record->getComments());

        $this->assertStringContainsString($expectedTitle, $reference);
    }

    public function testReferenceToSomeCommentsMustPointToFirstAddedComment(): void
    {
        $comments = $this->record->getComments();
        $firstAddedComment = $comments->findFirstAdded();

        $expectedReferenceUrl = $this->generateCommentUrl($firstAddedComment);

        $reference = $this->commentReferenceFormatter->formatReferences($comments);

        $this->assertStringContainsString($expectedReferenceUrl, $reference);
    }

    public function testReferenceToCommentWithUrlShouldNotContainsLink(): void
    {
        $comment = $this->createCommentWithText('foo http://foo/bar bar', $this->record);
        $reference = $this->commentReferenceFormatter->formatReference($comment);

        $this->assertStringNotContainsString('href="http://foo/bar"', $reference);
        $this->assertStringContainsString('foo http://foo/bar bar', $reference);
    }

    public function testReferenceToCommentShouldNotContainsMention(): void
    {
        $author = $this->record->getAuthor()->getUsername();
        $text = sprintf('text @%s mention', $author);

        $comment = $this->createCommentWithText($text, $this->record);
        $reference = $this->commentReferenceFormatter->formatReference($comment);

        $this->assertStringNotContainsString('@', $reference);
    }

    private function generateCommentUrl(Comment $comment): string
    {
        return $this->urlGenerator->generate('article_view', [
            'article' => $comment->getRecord()->getId(),
            '_fragment' => 'comment'.$comment->getSlug(),
        ]);
    }

    private function createCommentWithText(string $text, Record $record): Comment
    {
        $commentId = Uuid::uuid4();

        $stub = $this->createMock(Comment::class);
        $stub
            ->method('getId')
            ->willReturn($commentId);
        $stub
            ->method('getSlug')
            ->willReturn('fpfyRTmt6XeE9ehEKZ5LwF');
        $stub
            ->method('getText')
            ->willReturn($text);
        $stub
            ->method('getRecord')
            ->willReturn($record);

        return $stub;
    }
}
