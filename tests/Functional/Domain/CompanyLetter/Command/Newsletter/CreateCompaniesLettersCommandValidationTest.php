<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\Newsletter;

use App\Domain\CompanyLetter\Command\Newsletter\SendCompaniesLettersCommand;
use App\Domain\CompanyLetter\Entity\CompanyLetter;
use Exception;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\CompanyLetter\LoadCompanyLetterForPreviousMonth;
use Tests\Functional\ValidationTestCase;

class CreateCompaniesLettersCommandValidationTest extends ValidationTestCase
{
    public function testCorrectCompanyLetterId(): void
    {
        $command = new SendCompaniesLettersCommand(Uuid::uuid4());

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('companyLetterId', 'Такой рассылки не существует.');
    }

    /**
     * @throws Exception
     */
    public function testInvalidCompanyLetterId(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyLetterForPreviousMonth::class,
        ])->getReferenceRepository();

        $companyLetter = $referenceRepository->getReference(LoadCompanyLetterForPreviousMonth::REFERENCE_NAME);
        assert($companyLetter instanceof CompanyLetter);

        $companyLetterCorrectId = $companyLetter->getId();

        $command = new SendCompaniesLettersCommand($companyLetterCorrectId);

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
