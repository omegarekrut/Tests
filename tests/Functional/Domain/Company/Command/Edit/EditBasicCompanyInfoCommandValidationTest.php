<?php

namespace Tests\Functional\Domain\Company\Command\Edit;

use App\Domain\Company\Collection\RubricCollection;
use App\Domain\Company\Command\Edit\EditBasicCompanyInfoCommand;
use App\Domain\Company\Entity\Company;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsCompany;
use Tests\Functional\ValidationTestCase;

class EditBasicCompanyInfoCommandValidationTest extends ValidationTestCase
{
    private EditBasicCompanyInfoCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTackleShopsCompany::class,
        ])->getReferenceRepository();

        /** @var Company $company */
        $company = $referenceRepository->getReference(LoadTackleShopsCompany::REFERENCE_NAME);

        $this->command = new EditBasicCompanyInfoCommand($company);
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->command->name = '';
        $this->command->scopeActivity = '';
        $this->command->rubrics = new RubricCollection();

        $this->getValidator()->validate($this->command);

        $errors = $this->getValidator()->getLastErrors();

        $this->assertCount(3, $errors);

        foreach ($errors as $error) {
            $this->assertEquals('Поле не должно быть пустым', $error->getMessage());
        }
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

    public function testValid(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
