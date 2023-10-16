<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Command\RewriteCompanyAuthorNameCommand;
use App\Domain\Company\Entity\Company;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsGenerateCompany;
use Tests\Functional\ValidationTestCase;

/**
 * @group company
 */
class RewriteCompanyAuthorNameCommandValidationTest extends ValidationTestCase
{
    /**
     * @throws \Exception
     */
    public function testValidationNotPassedForIncorrectIdFilledCommand(): void
    {
        $command = new RewriteCompanyAuthorNameCommand(Uuid::uuid4());

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('companyId', 'Компания не найдена.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTackleShopsGenerateCompany::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadTackleShopsGenerateCompany::getRandReferenceName());
        assert($company instanceof Company);

        $command = new RewriteCompanyAuthorNameCommand($company->getId());

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
