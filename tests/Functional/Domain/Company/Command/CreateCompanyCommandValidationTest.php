<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Collection\RubricCollection;
use App\Domain\Company\Command\CreateCompanyCommand;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsCompany;
use Tests\DataFixtures\ORM\Company\Rubric\LoadTackleShopsRubric;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\Functional\ValidationTestCase;

/**
 * @group company-create
 */
class CreateCompanyCommandValidationTest extends ValidationTestCase
{
    /**
     * @var CreateCompanyCommand
     */
    private $command;

    private $referenceRepository;

    private $rubric;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadTackleShopsCompany::class,
            LoadTackleShopsRubric::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $adminUser = $this->referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        $this->rubric = $this->referenceRepository->getReference(LoadTackleShopsRubric::REFERENCE_NAME);
        $this->command = new CreateCompanyCommand(Uuid::uuid4(), $adminUser);
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->command
        );

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['id', 'user', 'name', 'scopeActivity', 'rubrics'],
            null,
            'Это поле обязательно для заполнения'
        );
    }

    public function testMustBeUser(): void
    {
        $this->command->user = $this;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('user', sprintf('Тип значения должен быть %s.', User::class));
    }

    public function testNotBlankRubrics(): void
    {
        $this->command->name = $this->getFaker()->name;
        $this->command->scopeActivity = $this->getFaker()->companySuffix;
        $this->command->rubrics = new RubricCollection();

        $this->getValidator()->validate($this->command);
        $this->assertFieldInvalid('rubrics', 'Вы должны выбрать хотя бы 1 рубрику');
    }

    public function testInvalidLengthName(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['name'],
            $this->getFaker()->realText(500),
            'Длина не должна превышать 50 символов'
        );
    }

    public function testInvalidLengthScopeActivity(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['scopeActivity'],
            $this->getFaker()->realText(500),
            'Длина не должна превышать 200 символов'
        );
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->name = $this->getFaker()->name;
        $this->command->scopeActivity = $this->getFaker()->companySuffix;
        $this->command->rubrics = new RubricCollection([$this->rubric]);

        $this->getValidator()->validate($this->command);
        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
