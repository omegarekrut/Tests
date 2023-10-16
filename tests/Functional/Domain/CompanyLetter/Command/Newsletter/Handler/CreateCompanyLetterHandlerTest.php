<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\Newsletter\Handler;

use App\Domain\CompanyLetter\Command\Newsletter\CreateCompanyLetterCommand;
use App\Domain\CompanyLetter\Entity\ValueObject\CompanyLetterPeriod;
use App\Domain\CompanyLetter\Exception\CompanyLetterForPeriodAlreadyExistException;
use App\Domain\CompanyLetter\Repository\CompanyLetterRepository;
use Exception;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\CompanyLetter\LoadCompanyLetterForPreviousMonth;
use Tests\Functional\TestCase;

class CreateCompanyLetterHandlerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            LoadCompanyLetterForPreviousMonth::class,
        ])->getReferenceRepository();
    }

    /**
     * @throws Exception
     */
    public function testHandle(): void
    {
        $expectedCompanyLetterUuid = Uuid::uuid4();

        $companyLetterRepository = $this->getContainer()->get(CompanyLetterRepository::class);
        assert($companyLetterRepository instanceof CompanyLetterRepository);

        $createCompanyLetterCommand = new CreateCompanyLetterCommand(
            CompanyLetterPeriod::createLastAccessibleCompanyLetterPeriod(),
            $expectedCompanyLetterUuid
        );
        $this->getCommandBus()->handle($createCompanyLetterCommand);

        $newCompanyLetter = $companyLetterRepository->findLastCompanyLetter();

        $this->assertEquals($expectedCompanyLetterUuid, $newCompanyLetter->getId());
    }

    /**
     * @throws Exception
     */
    public function testCreateTwoCompanyLetterInSamePeriodShouldBeThrowException(): void
    {
        $companyLetterUuid = Uuid::uuid4();

        $this->expectException(CompanyLetterForPeriodAlreadyExistException::class);
        $this->expectExceptionMessage('CompanyLetter period exception');

        $createCompanyLetterCommand = new CreateCompanyLetterCommand(
            CompanyLetterPeriod::createPreviousCompanyLetterPeriod(),
            $companyLetterUuid
        );

        $this->getCommandBus()->handle($createCompanyLetterCommand);
    }
}
