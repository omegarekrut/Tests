<?php

namespace Tests\Functional\Domain\Comment\Command\Handler;

use App\Domain\Comment\Command\CreateCommentCommand;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\Notification\CommentOnCommentedRecordNotification;
use App\Domain\User\Entity\Notification\CommentOnRecordNotification;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Comment\LoadCommentWithMentionedUser;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group comment
 */
class CreateCommentHandlerTest extends TestCase
{
    /** @var Record */
    private $record;
    /** @var CreateCommentCommand */
    private $createCommentCommand;
    /** @var Comment */
    private $parentComment;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadTestUser::class,
            LoadCommentWithMentionedUser::class,
        ])->getReferenceRepository();

        /** @var AuthorInterface $commentator */
        $commentator = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($commentator instanceof AuthorInterface);

        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        assert($this->record instanceof Record);

        $this->parentComment = $referenceRepository->getReference(LoadCommentWithMentionedUser::REFERENCE_NAME);
        assert($this->parentComment instanceof Comment);

        $this->createCommentCommand = new CreateCommentCommand($commentator);
        $this->createCommentCommand->commentId = Uuid::uuid4();
        $this->createCommentCommand->text = 'some comment text';
        $this->createCommentCommand->recordId = $this->record->getId();
        $this->createCommentCommand->images = new ImageWithRotationAngleCollection([]);
    }

    protected function tearDown(): void
    {
        unset(
            $this->record,
            $this->parentComment,
            $this->createCommentCommand
        );

        parent::tearDown();
    }

    public function testCommentMustBeAddedToRecordAfterHandling(): void
    {
        $this->getCommandBus()->handle($this->createCommentCommand);

        /** @var Comment|null $actualComment */
        $actualComment = $this->record->getCommentsWithAnswers()->last();

        $this->assertNotEmpty($actualComment);
        $this->assertEquals($this->createCommentCommand->text, $actualComment->getText());
        $this->assertSame($this->createCommentCommand->recordId, $actualComment->getRecord()->getId());
    }

    public function testAnswerToCommentMustBeAdded(): void
    {
        $this->createCommentCommand->parentCommentSlug = $this->parentComment->getSlug();
        $this->getCommandBus()->handle($this->createCommentCommand);

        /** @var Comment|null $actualComment */
        $actualComment = $this->record->getCommentsWithAnswers()->last();

        $this->assertNotEmpty($actualComment);
        $this->assertEquals($this->createCommentCommand->text, $actualComment->getText());
        $this->assertSame($this->createCommentCommand->recordId, $actualComment->getRecord()->getId());
    }

    /**
     * @group notification
     */
    public function testAfterCommentPublicationRecordAuthorShouldGetNotification(): void
    {
        $this->getCommandBus()->handle($this->createCommentCommand);

        $recordAuthor = $this->record->getAuthor();

        $this->assertInstanceOf(User::class, $recordAuthor);
        /** @var User $recordAuthor */

        /** @var Notification|CommentOnRecordNotification|null $actualNotification */
        $actualNotification = $recordAuthor->getUnreadNotifications()->first();

        $this->assertNotEmpty($actualNotification);
        $this->assertInstanceOf(CommentOnRecordNotification::class, $actualNotification);
    }

    /**
     * @group notification
     */
    public function testAfterCommentPublicationRecordCommentatorsShouldGetNotifications(): void
    {
        $this->getCommandBus()->handle($this->createCommentCommand);

        $comment = $this->record->getCommentsWithAnswers()->last();

        $commentatorsWithoutRecordAuthor = $this->record
            ->getCommentsWithAnswers()
            ->getUniqueAuthors()
            ->filter(function (AuthorInterface $author) use ($comment) {
                return $author !== $this->record->getAuthor() && $author !== $comment->getAuthor();
            });

        $this->assertGreaterThanOrEqual(1, count($commentatorsWithoutRecordAuthor));

        /** @var User $commentator */
        foreach ($commentatorsWithoutRecordAuthor as $commentator) {
            /** @var Notification|CommentOnCommentedRecordNotification|null $actualNotification */
            $actualNotification = $commentator->getUnreadNotifications()->first();

            $this->assertNotEmpty($actualNotification);
            $this->assertInstanceOf(CommentOnCommentedRecordNotification::class, $actualNotification);
        }
    }
}
