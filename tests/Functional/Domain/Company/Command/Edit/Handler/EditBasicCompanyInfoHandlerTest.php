<?php

namespace Tests\Functional\Domain\Company\Command\Edit\Handler;

use App\Domain\Company\Collection\RubricCollection;
use App\Domain\Company\Command\Edit\EditBasicCompanyInfoCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Rubric;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsCompany;
use Tests\DataFixtures\ORM\Company\Rubric\LoadAquaMotorcycleShopsRubric;
use Tests\Functional\TestCase;

class EditBasicCompanyInfoHandlerTest extends TestCase
{
    private Company $company;

    private const COMPANY_NEW_NAME = 'company-new-name';
    private const COMPANY_NEW_SCOPE_ACTIVITY = 'company-new-scope-activity';
    private Rubric $newRubric;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTackleShopsCompany::class,
            LoadAquaMotorcycleShopsRubric::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadTackleShopsCompany::REFERENCE_NAME);
        $this->newRubric = $referenceRepository->getReference(LoadAquaMotorcycleShopsRubric::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->company,
            $this->newRubric
        );

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $command = new EditBasicCompanyInfoCommand($this->company);

        $command->name = self::COMPANY_NEW_NAME;
        $command->scopeActivity = self::COMPANY_NEW_SCOPE_ACTIVITY;
        $command->rubrics = new RubricCollection([$this->newRubric]);

        $this->getCommandBus()->handle($command);

        $this->assertEquals(self::COMPANY_NEW_NAME, $this->company->getName());
        $this->assertEquals(self::COMPANY_NEW_SCOPE_ACTIVITY, $this->company->getScopeActivity());
        $this->assertCount(1, $this->company->getRubrics());
        $this->assertContains($this->newRubric, $this->company->getRubrics());
    }
}
