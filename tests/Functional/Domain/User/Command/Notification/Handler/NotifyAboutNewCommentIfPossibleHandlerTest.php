<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Command\Notification\NotifyRecordAuthorAboutNewCommentIfPossibleCommand;
use App\Domain\User\Entity\Notification\CommentOnRecordNotification;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\User;
use App\Module\Author\AnonymousAuthor;
use App\Module\Author\AuthorInterface;
use PHPUnit\Framework\SkippedTestError;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifyAboutNewCommentIfPossibleHandlerTest extends TestCase
{
    private Record $record;
    private Comment $comment;
    private Comment $commentForCompanyArticle;
    private CompanyArticle $companyArticle;
    private User $companyEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadAquaMotorcycleShopsCompanyArticle::class,
            LoadAquaMotorcycleShopsCompany::class,
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->comment = $this->findCommentCommentNotBelongingToAuthor(
            $this->record->getComments(),
            $this->record->getAuthor()
        );
        $this->companyArticle = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompanyArticle::REFERENCE_NAME);
        $this->companyEmployee = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $this->commentForCompanyArticle = $this->findCommentCommentNotBelongingToAuthor(
            $this->companyArticle->getComments(),
            $this->companyArticle->getAuthor()
        );
        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);

        $company->addEmployee($this->companyEmployee);
    }

    protected function tearDown(): void
    {
        unset(
            $this->record,
            $this->comment,
            $this->companyArticle,
            $this->companyEmployee,
            $this->commentForCompanyArticle
        );

        parent::tearDown();
    }

    public function testAfterHandlingRecordAuthorMustGetNotification(): void
    {
        $command = new NotifyRecordAuthorAboutNewCommentIfPossibleCommand($this->record, $this->comment->getId());

        $this->getCommandBus()->handle($command);

        /** @var Notification|CommentOnRecordNotification|null $actualNotification */
        $actualNotification = $this->record->getAuthor()->getUnreadNotifications()->first();

        $this->assertNotEmpty($actualNotification);
        $this->assertInstanceOf(CommentOnRecordNotification::class, $actualNotification);
        $this->assertTrue($this->record === $actualNotification->getOwnerRecord());
        $this->assertTrue($this->comment === $actualNotification->getComment());
        $this->assertTrue($this->comment->getAuthor() === $actualNotification->getInitiator());
    }

    public function testAfterCompanyArticleHandlingCompanyEmployeesMustGetNotification(): void
    {
        $command = new NotifyRecordAuthorAboutNewCommentIfPossibleCommand(
            $this->companyArticle,
            $this->commentForCompanyArticle->getId()
        );

        $this->getCommandBus()->handle($command);

        /** @var Notification|CommentOnRecordNotification|null $actualNotification */
        $actualNotification = $this->companyEmployee->getUnreadNotifications()->first();

        $this->assertNotEmpty($actualNotification);
        $this->assertInstanceOf(CommentOnRecordNotification::class, $actualNotification);
        $this->assertTrue($this->companyArticle === $actualNotification->getOwnerRecord());
        $this->assertTrue($this->commentForCompanyArticle === $actualNotification->getComment());
        $this->assertTrue($this->commentForCompanyArticle->getAuthor() === $actualNotification->getInitiator());
    }

    public function testNotificationMustBeSkipIfCommentIsNotExists(): void
    {
        $command = new NotifyRecordAuthorAboutNewCommentIfPossibleCommand($this->record, Uuid::uuid4());

        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $this->record->getAuthor()->getUnreadNotifications());
    }

    public function testNotificationMustBeSkipIfCommentAuthorEqualsRecordAuthor(): void
    {
        $comment = $this->record->addComment(Uuid::uuid4(), 'someslug', 'some comment', $this->record->getAuthor());
        $command = new NotifyRecordAuthorAboutNewCommentIfPossibleCommand($this->record, $comment->getId());

        $this->getCommandBus()->handle($command);

        $actualNotification = $this->record->getAuthor()->getUnreadNotifications()->first();

        $this->assertEmpty($actualNotification);
    }

    public function testNotificationMustBeSkipIfRecordAuthorIsNotUser(): void
    {
        $record = $this->createRecord(new AnonymousAuthor('anon.'));
        $command = new NotifyRecordAuthorAboutNewCommentIfPossibleCommand($record, $this->comment->getId());

        $this->getCommandBus()->handle($command);

        $actualNotification = $this->record->getAuthor()->getUnreadNotifications()->first();

        $this->assertEmpty($actualNotification);
    }

    private function createRecord(AuthorInterface $author): Record
    {
        $stub = $this->createMock(Record::class);
        $stub
            ->method('getAuthor')
            ->willReturn($author);

        return $stub;
    }

    private function findCommentCommentNotBelongingToAuthor(CommentCollection $comments, AuthorInterface $author): Comment
    {
        foreach ($comments as $comment) {
            if ($comment->getAuthor() !== $author) {
                return $comment;
            }
        }

        throw new SkippedTestError('Could not find comment not belonging to the author of the record.');
    }
}
