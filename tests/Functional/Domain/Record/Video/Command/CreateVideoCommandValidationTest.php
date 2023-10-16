<?php

namespace Tests\Functional\Domain\Record\Video\Command;

use App\Domain\Category\Entity\Category;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Video\Command\CreateVideoCommand;
use App\Domain\Region\Entity\Region;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\ValidationTestCase;

/**
 * @group video
 */
class CreateVideoCommandValidationTest extends ValidationTestCase
{
    private CreateVideoCommand $command;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadAquaMotorcycleShopsCompany::class,
            LoadTestRegion::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $author = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($author instanceof User);

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $this->command = new CreateVideoCommand($author);
        $this->command->category = $this->createMock(Category::class);
        $this->command->videoUrl = 'https://www.youtube.com/watch?v=nL5d37iqWIU&t=1s';
        $this->command->title = 'title';
        $this->command->description = 'text';
        $this->command->regionId = $region->getId();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['category', 'videoUrl', 'title', 'description'], null, 'Это поле обязательно для заполнения.');
    }

    public function testPreviewContainTooMuchUpperCase(): void
    {
        $description = mb_strtoupper($this->getFaker()->realText(50));

        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'description'], $description, 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testInvalidLength(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title'], $this->getFaker()->realText(500), 'Длина не должна превышать 255 символов.');
    }

    public function testInvalidUrl(): void
    {
        $this->command->videoUrl = $this->getFaker()->realText(15);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('videoUrl', 'Значение не является допустимым URL.');
    }

    public function testInvalidRegionId(): void
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
