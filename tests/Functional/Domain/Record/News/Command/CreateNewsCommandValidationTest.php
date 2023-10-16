<?php

namespace Tests\Functional\Domain\Record\News\Command;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\News\Command\CreateNewsCommand;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\Image;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\ValidationTestCase;

/**
 * @group news
 */
class CreateNewsCommandValidationTest extends ValidationTestCase
{
    private CreateNewsCommand $command;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $author = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($author instanceof User);

        $this->command = new CreateNewsCommand();
        $this->command->author = $author;
        $this->command->title = 'title';
        $this->command->preview = 'preview';
        $this->command->image = $this->createMock(Image::class);
        $this->command->text = 'text';
        $this->command->priority = 1;
        $this->command->actual = $this->createMock(\DateTime::class);
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $requiredFields = ['title', 'preview', 'text', 'priority', 'image', 'author'];

        $this->assertOnlyFieldsAreInvalid($this->command, $requiredFields, null, 'Это поле обязательно для заполнения.');
    }

    public function testPreviewContainTooMuchUpperCase(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['preview'], mb_strtoupper($this->getFaker()->realText(50)), 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testInvalidLengthFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов.');
        $this->assertOnlyFieldsAreInvalid($this->command, ['preview'], $this->getFaker()->realText(350), 'Длина не должна превышать 300 символов.');
    }

    public function testInvalidTypeFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['priority'], 'test', 'Это значение должно быть числом.');
    }

    public function testOnMinValue(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['priority'], -1, 'Приоритет не может быть меньше 0');
    }

    public function testAuthorIsNotOwnerCompany(): void
    {
        $this->command->companyAuthor = $this->company;

        $this->getValidator()->validate($this->command);

        $validationErrors = $this->getValidator()->getLastErrors();

        foreach ($validationErrors as $error) {
            $this->assertEquals(
                sprintf('Вам не разрешено публиковать записи от имени компании %s.', $this->company->getName()),
                $error->getMessage()
            );
        }
    }

    public function testAuthorIsOwnerCompany(): void
    {
        $this->command->author = $this->company->getOwner();
        $this->command->companyAuthor = $this->company;

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
