<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\Newsletter\Handler;

use App\Domain\CompanyLetter\Command\Newsletter\CreateCompanyLetterCommand;
use App\Domain\CompanyLetter\Command\Newsletter\SendCompaniesLettersCommand;
use App\Domain\CompanyLetter\Entity\ValueObject\CompanyLetterPeriod;
use App\Domain\CompanyLetter\Repository\CompanyLetterRepository;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\Functional\TestCase;

class SendCompaniesLettersHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $this->loadFixtures([
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $expectedCompanyLetterUuid = Uuid::uuid4();

        $createCompanyLetterCommand = new CreateCompanyLetterCommand(
            CompanyLetterPeriod::createLastAccessibleCompanyLetterPeriod(),
            $expectedCompanyLetterUuid
        );
        $this->getCommandBus()->handle($createCompanyLetterCommand);

        $sendCompanyLetterCommand = new SendCompaniesLettersCommand($expectedCompanyLetterUuid);
        $this->getCommandBus()->handle($sendCompanyLetterCommand);

        $companyLetterRepository = $this->getContainer()->get(CompanyLetterRepository::class);
        assert($companyLetterRepository instanceof CompanyLetterRepository);
        $newCompanyLetter = $companyLetterRepository->findLastCompanyLetter();

        $this->assertEquals($expectedCompanyLetterUuid, $newCompanyLetter->getId());
        $this->assertNotNull($newCompanyLetter->getSendingDate());
        $this->assertGreaterThan(0, $newCompanyLetter->getNumberOfRecipients());
    }
}
