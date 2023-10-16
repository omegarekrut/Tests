<?php

namespace Tests\Functional\Domain\Record\Gallery\Command;

use App\Domain\Category\Entity\Category;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Gallery\Command\CreateGalleryCommand;
use App\Domain\Region\Entity\Region;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;
use Tests\Functional\ValidationTestCase;

/**
 * @group gallery
 */
class CreateGalleryCommandValidationTest extends ValidationTestCase
{
    private CreateGalleryCommand $command;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadAquaMotorcycleShopsCompany::class,
            LoadNovosibirskRegion::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $author = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($author instanceof User);

        $region = $referenceRepository->getReference(LoadNovosibirskRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $this->command = new CreateGalleryCommand($author);
        $this->command->category = $this->createMock(Category::class);
        $this->command->title = 'title';
        $this->command->data = 'preview';
        $this->command->imageName = 'imageName.png';
        $this->command->imageRotationAngle = 90;
        $this->command->regionId = (string) $region->getId();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->command->title = null;
        $this->command->category = null;
        $this->command->imageName = null;
        $this->command->data = '';
        $this->command->imageRotationAngle = '';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Значение не должно быть пустым.');
        $this->assertFieldInvalid('category', 'Значение не должно быть пустым.');
        $this->assertFieldInvalid('imageName', 'Значение не должно быть пустым.');
    }

    public function testInvalidLength(): void
    {
        $this->command->title = $this->getFaker()->realText(300);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Длина не должна превышать 255 символов.');
    }

    public function testInvalidCategoryType(): void
    {
        $expectedViolationMessage = sprintf('Тип значения должен быть %s.', Category::class);

        $this->command->category = '-';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('category', $expectedViolationMessage);
    }

    public function testRotationAngleCannotBeLessThanZero(): void
    {
        $this->command->imageRotationAngle = -1;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('imageRotationAngle', 'Значение должно быть 0 или больше.');
    }

    public function testRotationAngleCannotBeGreaterThan360(): void
    {
        $this->command->imageRotationAngle = 361;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('imageRotationAngle', 'Значение должно быть 360 или меньше.');
    }

    public function testTitleAndPreviewContainTooMuchUpperCase(): void
    {
        $this->command->title = mb_strtoupper($this->getFaker()->realText(50));
        $this->command->data = mb_strtoupper($this->getFaker()->realText(50));

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
        $this->assertFieldInvalid('data', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testRegionMustExist(): void
    {
        $this->command->regionId = Uuid::uuid4()->toString();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('regionId', 'Регион места съемки не найден.');
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
