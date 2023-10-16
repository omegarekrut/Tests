<?php

namespace Tests\Functional\Domain\Company\Command\Rubric;

use App\Domain\Company\Command\Rubric\CreateRubricCommand;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Rubric\LoadPaidReservoirsRubric;
use Tests\Functional\ValidationTestCase;

class CreateRubricCommandValidationTest extends ValidationTestCase
{
    private CreateRubricCommand $command;

    private ReferenceRepository $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadPaidReservoirsRubric::class,
        ])->getReferenceRepository();

        $this->command = new CreateRubricCommand(Uuid::uuid4());
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->command
        );

        parent::tearDown();
    }

    public function testNotUniqueSlug(): void
    {
        $rubric = $this->referenceRepository->getReference(LoadPaidReservoirsRubric::REFERENCE_NAME);
        $this->command->slug = $rubric->getSlug();

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
        $this->command->priority = $this->getFaker()->randomNumber();

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
