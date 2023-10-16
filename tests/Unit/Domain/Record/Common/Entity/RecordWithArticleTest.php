<?php

namespace Tests\Unit\Domain\Record\Common\Entity;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Collection\RecordSemanticLinkCollection;
use App\Domain\Record\Common\Entity\RecordSemanticLink;
use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Tests\Unit\Mock\Entity\RecordMock;
use Tests\Unit\TestCase;

/**
 * @group record
 */
class RecordWithArticleTest extends TestCase
{
    public function testGetSemanticLink(): void
    {
        $record = $this->createRecord();

        $this->assertEmpty($record->getRecordSemanticLinks());
    }

    public function testIsAttachedSemanticLink(): void
    {
        $record = $this->createRecord();

        $recordSemanticLink = $this->createRecordSemanticLink($record);
        $record->attachUniqueRecordSemanticLink($recordSemanticLink);

        $this->assertTrue($record->isAttachedRecordSemanticLink($recordSemanticLink));
    }

    public function testAttachUniqueSemanticLink(): void
    {
        $record = $this->createRecord();

        $recordSemanticLinkUnique = $this->createRecordSemanticLink($record);

        $record->attachUniqueRecordSemanticLink($recordSemanticLinkUnique);
        $record->attachUniqueRecordSemanticLink($recordSemanticLinkUnique);

        $this->assertNotEmpty($record->getRecordSemanticLinks());
        $this->assertCount(1, $record->getRecordSemanticLinks());
        $this->assertTrue($record->isAttachedRecordSemanticLink($recordSemanticLinkUnique));
    }

    public function testSyncSemanticLinks(): void
    {
        $record = $this->createRecord();

        $recordSemanticLinkDetached = $this->createRecordSemanticLink($record);
        $recordSemanticLinkAttached = $this->createRecordSemanticLink($record);
        $recordSemanticLinks = new RecordSemanticLinkCollection([$recordSemanticLinkAttached]);

        $record->attachUniqueRecordSemanticLink($recordSemanticLinkDetached);
        $record->attachUniqueRecordSemanticLink($recordSemanticLinkAttached);

        $record->syncRecordSemanticLinks($recordSemanticLinks);

        $this->assertNotEmpty($record->getRecordSemanticLinks());
        $this->assertCount(1, $record->getRecordSemanticLinks());
        $this->assertTrue($record->isAttachedRecordSemanticLink($recordSemanticLinkAttached));
    }

    public function testDetachRecordSemanticLinks(): void
    {
        $record = $this->createRecord();
        $recordSemanticLink = $this->createRecordSemanticLink($record);
        $semanticLink = $recordSemanticLink->getSemanticLink();

        $record->attachUniqueRecordSemanticLink($recordSemanticLink);

        $record->detachRecordSemanticLinks();

        $this->assertCount(0, $record->getRecordSemanticLinks());
        $this->assertEquals(0, $semanticLink->getNumberActiveLinks());
    }

    public function testHideCommentWhereCommentCollectionNotContainsComment(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Comment does not belong to the current comment collection');

        $record = $this->createRecord();
        $record->setAnswersCollection(new CommentCollection());

        $record->hideComment(
            $this->createMock(Comment::class),
            $this->createMock(User::class)
        );
    }

    public function testRestoreCommentWhereCommentCollectionNotContainsComment(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Comment does not belong to the current comment collection');

        $record = $this->createRecord();
        $record->setAnswersCollection(new CommentCollection());

        $record->restoreComment(
            $this->createMock(Comment::class)
        );
    }

    public function testHideCommentThreadWhereCommentCollectionNotContainsComment(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Comment does not belong to the current comment collection');

        $record = $this->createRecord();
        $record->setAnswersCollection(new CommentCollection());

        $record->hideCommentThread(
            $this->createMock(Comment::class),
            $this->createMock(User::class)
        );
    }

    private function createRecord(): Record
    {
        return new RecordMock(
            'Title',
            'description',
            $this->createMock(AuthorInterface::class)
        );
    }

    private function createRecordSemanticLink(Record $record): RecordSemanticLink
    {
        $semanticLink = new SemanticLink(
            Uuid::uuid4(),
            '/articles/view/88548',
            'description avoid message'
        );

        return new RecordSemanticLink(Uuid::uuid4(), $record, $semanticLink, $semanticLink->getText());
    }
}
