<?php

namespace Tests\Functional\Domain\Company\Command\Rubric;

use App\Domain\Company\Command\Rubric\UpdateRubricCommand;
use App\Domain\Company\Entity\Rubric;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Company\Rubric\LoadAquaMotorcycleShopsRubric;
use Tests\DataFixtures\ORM\Company\Rubric\LoadPaidReservoirsRubric;
use Tests\Functional\ValidationTestCase;

class UpdateRubricCommandValidationTest extends ValidationTestCase
{
    private UpdateRubricCommand $command;

    private ReferenceRepository $referenceRepository;

    private Rubric $aquaMotorcycleShopsRubric;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadPaidReservoirsRubric::class,
            LoadAquaMotorcycleShopsRubric::class,
        ])->getReferenceRepository();

        /** @var Rubric $paidReservoirsRubric */
        $paidReservoirsRubric = $this->referenceRepository->getReference(LoadPaidReservoirsRubric::REFERENCE_NAME);

        $this->command = new UpdateRubricCommand($paidReservoirsRubric);
        $this->aquaMotorcycleShopsRubric = $this->referenceRepository->getReference(LoadAquaMotorcycleShopsRubric::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->command,
            $this->aquaMotorcycleShopsRubric
        );

        parent::tearDown();
    }

    public function testNotUniqueSlug(): void
    {
        $this->command->slug = $this->aquaMotorcycleShopsRubric->getSlug();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('slug', "Рубрика '{$this->command->slug}' уже существует.");
    }

    public function testInvalidSlugFormat(): void
    {
        $this->command->slug = $this->getFaker()->name;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid(
            'slug',
            'Разрешены только латинские буквы, цифры, \'-\'.'
        );
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['id', 'slug', 'name'],
            null,
            'Это поле обязательно для заполнения'
        );
    }

    public function testInvalidLength(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['slug', 'name'],
            $this->getFaker()->realText(500),
            'Длина не должна превышать 255 символов'
        );
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->slug = $this->getFaker()->slug;
        $this->command->name = $this->getFaker()->name;

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
