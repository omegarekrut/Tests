<?php

namespace Tests\Functional\Domain\Comment\Command;

use App\Domain\Comment\Command\CreateCommentCommand;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Entity\Record;
use App\Module\Author\AuthorInterface;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Comment\LoadCommentWithMentionedUser;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group comment
 */
class CreateCommentCommandValidationTest extends ValidationTestCase
{
    private CreateCommentCommand $command;
    private Record $record;
    private AuthorInterface $commentator;
    private Comment $parentComment;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadTestUser::class,
            LoadCommentWithMentionedUser::class,
        ])->getReferenceRepository();

        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        assert($this->record instanceof Record);

        $this->commentator = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($this->commentator instanceof AuthorInterface);

        $this->parentComment = $referenceRepository->getReference(LoadCommentWithMentionedUser::REFERENCE_NAME);
        assert($this->parentComment instanceof Comment);
    }

    protected function tearDown(): void
    {
        unset(
            $this->record,
            $this->commentator
        );

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $command = new CreateCommentCommand($this->commentator);

        $this->assertOnlyFieldsAreInvalid(
            $command,
            ['commentId', 'text', 'recordId'],
            null,
            'Это поле обязательно для заполнения'
        );
    }

    public function testMustBeUuid(): void
    {
        $this->command = new CreateCommentCommand($this->commentator);
        $this->command->text = 'some comment text';
        $this->command->recordId = $this->record->getId();
        $this->command->commentId = 'some not valid id';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('commentId', 'Тип значения должен быть Ramsey\Uuid\UuidInterface.');
    }

    public function testMustBeNumeric(): void
    {
        $this->command = new CreateCommentCommand($this->commentator);
        $this->command->text = 'some comment text';
        $this->command->recordId = $this->record;
        $this->command->commentId = Uuid::uuid4();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('recordId', 'Тип значения должен быть numeric.');
    }

    public function testMustBeRecord(): void
    {
        $this->command = new CreateCommentCommand($this->commentator);
        $this->command->text = 'some comment text';
        $this->command->recordId = 20000;
        $this->command->commentId = Uuid::uuid4();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('recordId', 'Запись для комментирования не найдена.');
    }

    public function testTextToUpperCase(): void
    {
        $this->command = new CreateCommentCommand($this->commentator);
        $this->command->commentId = Uuid::uuid4();
        $this->command->text = 'SOME UPPER CASE TEXT';
        $this->command->recordId = $this->record;

        $this->getValidator()->validate($this->command);
        $this->assertFieldInvalid('text', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testNotActualParentCommentSlug(): void
    {
        $this->command = new CreateCommentCommand($this->commentator);
        $this->command->commentId = Uuid::uuid4();
        $this->command->text = 'some comment text';
        $this->command->recordId = $this->record->getId();
        $this->command->parentCommentSlug = 'someText';

        $this->getValidator()->validate($this->command);
        $this->assertFieldInvalid('parentCommentSlug', 'Исходный комментарий не найден.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command = new CreateCommentCommand($this->commentator);
        $this->command->commentId = Uuid::uuid4();
        $this->command->text = 'some comment text';
        $this->command->recordId = $this->record->getId();
        $this->command->parentCommentSlug = $this->parentComment->getSlug();

        $this->getValidator()->validate($this->command);
        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    public function testValidationShouldBePassedForCorrectFilledCommandWithoutParentComment(): void
    {
        $this->command = new CreateCommentCommand($this->commentator);
        $this->command->commentId = Uuid::uuid4();
        $this->command->text = 'some comment text';
        $this->command->recordId = $this->record->getId();

        $this->getValidator()->validate($this->command);
        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
