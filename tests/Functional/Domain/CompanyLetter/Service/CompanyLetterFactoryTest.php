<?php

namespace Tests\Functional\Domain\CompanyLetter\Service;

use App\Domain\CompanyLetter\Entity\GreetingBlock;
use App\Domain\CompanyLetter\Entity\InnovationBlock;
use App\Domain\CompanyLetter\Entity\ValueObject\CompanyLetterPeriod;
use App\Domain\CompanyLetter\Service\CompanyLetterFactory;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\CompanyLetter\LoadGreetingBlockPreviousMonth;
use Tests\DataFixtures\ORM\CompanyLetter\LoadInnovationBlockPreviousMonth;
use Tests\Functional\TestCase;

class CompanyLetterFactoryTest extends TestCase
{
    /**
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function testCreateCompanyLetterForPeriod(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGreetingBlockPreviousMonth::class,
            LoadInnovationBlockPreviousMonth::class,
        ])->getReferenceRepository();

        $greetingBlock = $referenceRepository->getReference(LoadGreetingBlockPreviousMonth::REFERENCE_NAME);
        assert($greetingBlock instanceof GreetingBlock);

        $innovationBlock = $referenceRepository->getReference(LoadInnovationBlockPreviousMonth::REFERENCE_NAME);
        assert($innovationBlock instanceof InnovationBlock);

        $companyLetterFactory = $this->getContainer()->get(CompanyLetterFactory::class);
        assert($companyLetterFactory instanceof CompanyLetterFactory);

        $expectedCompanyLetterPeriod = CompanyLetterPeriod::createLastAccessibleCompanyLetterPeriod();
        $expectedCompanyLetterUuid = Uuid::uuid4();

        $companyLetterFactory = $companyLetterFactory->createCompanyLetterForPeriod(
            $expectedCompanyLetterPeriod,
            $expectedCompanyLetterUuid
        );

        $this->assertEquals($expectedCompanyLetterPeriod->getMonthOfMailing(), $companyLetterFactory->getPeriodDate());
        $this->assertEquals($expectedCompanyLetterUuid, $companyLetterFactory->getId());
        $this->assertEquals($greetingBlock, $companyLetterFactory->getGreetingBlock());
        $this->assertEquals($innovationBlock, $companyLetterFactory->getInnovationBlocks()[0]);
    }

    /**
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function testCreateCompanyLetterForPeriodWithoutGreetingBlock(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadInnovationBlockPreviousMonth::class,
        ])->getReferenceRepository();

        $innovationBlock = $referenceRepository->getReference(LoadInnovationBlockPreviousMonth::REFERENCE_NAME);
        assert($innovationBlock instanceof InnovationBlock);

        $companyLetterFactory = $this->getContainer()->get(CompanyLetterFactory::class);
        assert($companyLetterFactory instanceof CompanyLetterFactory);

        $expectedCompanyLetterPeriod = CompanyLetterPeriod::createLastAccessibleCompanyLetterPeriod();
        $expectedCompanyLetterUuid = Uuid::uuid4();

        $companyLetterFactory = $companyLetterFactory->createCompanyLetterForPeriod(
            $expectedCompanyLetterPeriod,
            $expectedCompanyLetterUuid
        );

        $this->assertEquals($expectedCompanyLetterPeriod->getMonthOfMailing(), $companyLetterFactory->getPeriodDate());
        $this->assertEquals($expectedCompanyLetterUuid, $companyLetterFactory->getId());
        $this->assertEquals($innovationBlock, $companyLetterFactory->getInnovationBlocks()[0]);
        $this->assertNull($companyLetterFactory->getGreetingBlock());
    }

    /**
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function testCreateCompanyLetterForPeriodWithoutInnovationBlock(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGreetingBlockPreviousMonth::class,
        ])->getReferenceRepository();

        $greetingBlock = $referenceRepository->getReference(LoadGreetingBlockPreviousMonth::REFERENCE_NAME);
        assert($greetingBlock instanceof GreetingBlock);

        $companyLetterFactory = $this->getContainer()->get(CompanyLetterFactory::class);
        assert($companyLetterFactory instanceof CompanyLetterFactory);

        $expectedCompanyLetterPeriod = CompanyLetterPeriod::createLastAccessibleCompanyLetterPeriod();
        $expectedCompanyLetterUuid = Uuid::uuid4();

        $companyLetterFactory = $companyLetterFactory->createCompanyLetterForPeriod(
            $expectedCompanyLetterPeriod,
            $expectedCompanyLetterUuid
        );

        $this->assertEquals($expectedCompanyLetterPeriod->getMonthOfMailing(), $companyLetterFactory->getPeriodDate());
        $this->assertEquals($expectedCompanyLetterUuid, $companyLetterFactory->getId());
        $this->assertEmpty($companyLetterFactory->getInnovationBlocks());
        $this->assertEquals($greetingBlock, $companyLetterFactory->getGreetingBlock());
    }
}
