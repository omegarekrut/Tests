<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\Newsletter;

use App\Domain\Company\Entity\Company;
use App\Domain\CompanyLetter\Command\Newsletter\SendCompanyLetterToCompanyCommand;
use App\Domain\CompanyLetter\Entity\CompanyLetter;
use Exception;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\CompanyLetter\LoadCompanyLetterForPreviousMonth;
use Tests\Functional\ValidationTestCase;

class SendCompanyLetterItemCommandValidationTest extends ValidationTestCase
{
    private string $correctCompanyId;
    private string $correctCompanyLetterId;

    public function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyLetterForPreviousMonth::class,
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $companyLetter = $referenceRepository->getReference(LoadCompanyLetterForPreviousMonth::REFERENCE_NAME);
        assert($companyLetter instanceof CompanyLetter);

        $this->correctCompanyId = $company->getId();
        $this->correctCompanyLetterId = $companyLetter->getId();
    }

    public function testCorrectCompanyLetterId(): void
    {
        $command = new SendCompanyLetterToCompanyCommand(
            $this->correctCompanyLetterId,
            $this->correctCompanyId
        );

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    /**
     * @throws Exception
     */
    public function testInvalidCompanyLetterId(): void
    {
        $invalidCompanyLetterId = Uuid::uuid4();

        $command = new SendCompanyLetterToCompanyCommand(
            $invalidCompanyLetterId,
            $this->correctCompanyId
        );

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('companyLetterId', 'Такой рассылки не существует.');
    }

    /**
     * @throws Exception
     */
    public function testInvalidCompanyId(): void
    {
        $invalidCompanyId = Uuid::uuid4();

        $command = new SendCompanyLetterToCompanyCommand(
            $this->correctCompanyLetterId,
            $invalidCompanyId
        );

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('companyId', 'Компании с таким id не существует.');
    }
}
