<?php

namespace Tests\Functional\Domain\Record\Article\Command;

use App\Domain\Category\Entity\Category;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Article\Command\CreateArticleCommand;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Image;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\ValidationTestCase;

/**
 * @group article
 */
class CreateArticleCommandValidationTest extends ValidationTestCase
{
    private CreateArticleCommand $command;
    private Company $company;
    private User $authorIsOwnerCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadAquaMotorcycleShopsCompany::class,
            LoadCategories::class,
        ])->getReferenceRepository();

        $authorIsNotOwnerCompany = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($authorIsNotOwnerCompany instanceof User);

        $category = $referenceRepository->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES));
        assert($category instanceof Category);

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $this->authorIsOwnerCompany = $this->company->getOwner();

        $this->command = new CreateArticleCommand($authorIsNotOwnerCompany);
        $this->command->title = 'Company article title created';
        $this->command->text = 'Company article text';
        $this->command->category = $category;
        $this->command->images = new ImageCollection([new Image('image.jpg')]);
    }

    protected function tearDown(): void
    {
        unset(
            $this->command,
            $this->authorIsOwnerCompany,
            $this->company,
        );

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->command->title = null;
        $this->command->text = null;
        $this->command->category = null;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Это поле обязательно для заполнения.');
        $this->assertFieldInvalid('text', 'Это поле обязательно для заполнения.');
        $this->assertFieldInvalid('category', 'Это поле обязательно для заполнения.');
    }

    public function testInvalidLengthFields(): void
    {
        $this->command->title = $this->getFaker()->realText(300);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Длина не должна превышать 255 символов.');
    }

    public function testContainTooMuchUpperCase(): void
    {
        $fakeText = mb_strtoupper($this->getFaker()->realText(50));
        $this->command->title = $fakeText;
        $this->command->text = $fakeText;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
        $this->assertFieldInvalid('text', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
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
        $this->command->author = $this->authorIsOwnerCompany;
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
