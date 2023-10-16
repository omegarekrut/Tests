<?php

namespace Tests\Functional\Domain\SuggestedNews\Command;

use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\ValidationTestCase;
use App\Domain\SuggestedNews\Command\CreateSuggestedNewsCommand;
use Ramsey\Uuid\Uuid;

/**
 * @group suggested-news
 */
class CreateSuggestedNewsCommandValidationTest extends ValidationTestCase
{
    private CreateSuggestedNewsCommand $createSuggestedNewsCommand;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $author = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->createSuggestedNewsCommand = new CreateSuggestedNewsCommand(Uuid::uuid4(), $author);
        $this->createSuggestedNewsCommand->fullText = 'Текст';
        $this->createSuggestedNewsCommand->title = 'Заголовок';
    }

    protected function tearDown(): void
    {
        unset($this->createSuggestedNewsCommand);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $requiredFields = ['title', 'fullText'];

        $this->assertOnlyFieldsAreInvalid($this->createSuggestedNewsCommand, $requiredFields, null, 'Это поле обязательно для заполнения.');
    }

    public function testInvalidLengthFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->createSuggestedNewsCommand, ['title'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов.');
    }

    public function testMustBeString(): void
    {
        $this->createSuggestedNewsCommand->title = Uuid::uuid4();
        $this->createSuggestedNewsCommand->fullText = Uuid::uuid4();

        $this->getValidator()->validate($this->createSuggestedNewsCommand);

        $this->assertFieldInvalid('title', 'Тип значения должен быть string.');
        $this->assertFieldInvalid('fullText', 'Тип значения должен быть string.');
    }

    public function testContainTooMuchUpperCase(): void
    {
        $fakeText = mb_strtoupper($this->getFaker()->realText(50));

        $this->assertOnlyFieldsAreInvalid($this->createSuggestedNewsCommand, ['fullText'], $fakeText, 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->getValidator()->validate($this->createSuggestedNewsCommand);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
