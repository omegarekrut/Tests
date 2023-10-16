<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Command\Notification\NotifyRecordCommentatorsAboutNewCommentIfPossibleCommand;
use App\Domain\User\Entity\Notification\CommentOnCommentedRecordNotification;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleWithManyComments;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifyRecordCommentatorsAboutNewCommentIfPossibleHandlerTest extends TestCase
{
    private Record $record;
    private Comment $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticleWithManyComments::class,
        ])->getReferenceRepository();

        $this->record = $referenceRepository->getReference(LoadArticleWithManyComments::REFERENCE_NAME);
        $this->comment = $this->record->getComments()->first();
    }

    protected function tearDown(): void
    {
        unset(
            $this->record,
            $this->comment
        );

        parent::tearDown();
    }

    public function testAfterHandlingRecordCommentatorsShouldGetNotifications(): void
    {
        $command = new NotifyRecordCommentatorsAboutNewCommentIfPossibleCommand($this->record, $this->comment->getId());

        $this->getCommandBus()->handle($command);

        $expectedReceivers = $this->getExpectedReceivers();

        $this->assertNotEmpty($expectedReceivers);

        foreach ($expectedReceivers as $receiver) {
            assert($receiver instanceof User);

            /** @var Notification|CommentOnCommentedRecordNotification|null $actualNotification */
            $actualNotification = $receiver->getUnreadNotifications()->first();

            $this->assertInstanceOf(CommentOnCommentedRecordNotification::class, $actualNotification);
            $this->assertSame($this->comment, $actualNotification->getComment());
            $this->assertSame($this->record, $actualNotification->getCommentedRecord());
        }
    }

    public function testNotificationMustBeSkipIfCommentIsNotExists(): void
    {
        $command = new NotifyRecordCommentatorsAboutNewCommentIfPossibleCommand($this->record, Uuid::uuid4());

        $this->getCommandBus()->handle($command);

        $expectedReceivers = $this->getExpectedReceivers();

        $this->assertNotEmpty($expectedReceivers);

        foreach ($expectedReceivers as $receiver) {
            assert($receiver instanceof User);

            /** @var Notification|CommentOnCommentedRecordNotification|null $actualNotification */
            $actualNotification = $receiver->getUnreadNotifications()->first();

            $this->assertEmpty($actualNotification);
        }
    }

    public function testRecordAuthorMustNotGetNotification(): void
    {
        $this->record->addComment(Uuid::uuid4(), 'someslug', 'some comment', $this->record->getAuthor());

        $command = new NotifyRecordCommentatorsAboutNewCommentIfPossibleCommand($this->record, $this->comment->getId());

        $this->getCommandBus()->handle($command);

        $this->assertEmpty($this->record->getAuthor()->getUnreadNotifications());
    }

    public function testCommentAuthorMustNotGetNotification(): void
    {
        $this->record->addComment(Uuid::uuid4(), 'someslug', 'some comment', $this->comment->getAuthor());

        $command = new NotifyRecordCommentatorsAboutNewCommentIfPossibleCommand($this->record, $this->comment->getId());

        $this->getCommandBus()->handle($command);

        $this->assertEmpty($this->comment->getAuthor()->getUnreadNotifications());
    }

    private function getExpectedReceivers(): ArrayCollection
    {
        return $this->record
            ->getComments()
            ->getUniqueAuthors()
            ->filter(function (AuthorInterface $author) {
                return $author !== $this->comment->getAuthor()
                    && $author !== $this->record->getAuthor()
                    && $author instanceof User;
            });
    }
}
