<?php

namespace Tests\Functional\Domain\Record\Gallery\Command;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Gallery\Command\UpdateGalleryCommand;
use App\Domain\Record\Gallery\Entity\Gallery;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;
use Tests\Functional\ValidationTestCase;

/**
 * @group gallery
 */
class UpdateGalleryCommandValidationTest extends ValidationTestCase
{
    private UpdateGalleryCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new UpdateGalleryCommand($this->createMock(Gallery::class));
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['category', 'title'], null, 'Это поле обязательно для заполнения.');
    }

    public function testInvalidLength(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов.');
    }

    public function testInvalidCategoryType(): void
    {
        $expectedViolationMessage = sprintf('Тип значения должен быть %s.', Category::class);

        $this->assertOnlyFieldsAreInvalid($this->command, ['category'], '-', $expectedViolationMessage);
    }

    public function testContainTooMuchUpperCase(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'data'], mb_strtoupper($this->getFaker()->realText(50)), 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testRotationAngleCannotBeLessThanZero(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['imageRotationAngle'], -1, 'Значение должно быть 0 или больше.');
    }

    public function testRotationAngleCannotBeGreaterThan360(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['imageRotationAngle'], 361, 'Значение должно быть 360 или меньше.');
    }

    public function testRegionMustExist(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['regionId'], Uuid::uuid4()->toString(), 'Регион места съемки не найден.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskRegion::class,
        ])->getReferenceRepository();

        $region = $referenceRepository->getReference(LoadNovosibirskRegion::REFERENCE_NAME);

        $this->command->category = $this->createMock(Category::class);
        $this->command->title = 'title';
        $this->command->data = 'text';
        $this->command->regionId = (string) $region->getId();

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
