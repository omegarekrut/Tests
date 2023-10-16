<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Command\DeleteCompanyCommand;
use App\Domain\Company\Entity\Company;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsCompany;
use Tests\Functional\ValidationTestCase;

class DeleteCompanyCommandValidationTest extends ValidationTestCase
{
    private Company $company;
    private ReferenceRepository $referenceRepository;
    private DeleteCompanyCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadTackleShopsCompany::class,
        ])->getReferenceRepository();

        $this->company = $this->referenceRepository->getReference(LoadTackleShopsCompany::REFERENCE_NAME);
        $this->command = new DeleteCompanyCommand();
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
            ['companyId'],
            null,
            'Это поле обязательно для заполнения'
        );
    }

    public function testMustBeCompanyId(): void
    {
        $this->command->companyId = Uuid::uuid4();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('companyId', 'Компания c таким id не найдена.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->companyId = $this->company->getId();

        $this->getValidator()->validate($this->command);
        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
