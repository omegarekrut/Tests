<?php

namespace Tests\Functional\Domain\Company\Command\Statistics;

use App\Domain\Company\Command\Statistics\IncreaseCountInStatisticsCompanyByTypeCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Rubric;
use App\Domain\Company\Entity\Statistics\ValueObject\StatisticsType;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;
use Tests\Functional\ValidationTestCase;

class IncreaseCountInStatisticsCompanyByTypeValidationTest extends ValidationTestCase
{
    private Company $company;
    private Rubric $rubric;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadDefaultRubric::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);
        $this->company = $company;

        $rubric = $referenceRepository->getReference(LoadDefaultRubric::REFERENCE_NAME);
        assert($rubric instanceof Rubric);
        $this->rubric = $rubric;
    }

    protected function tearDown(): void
    {
        unset(
            $this->company,
            $this->rubric,
        );

        parent::tearDown();
    }

    public function testCommandFilledWithCorrectDataShouldNotCauseErrors(): void
    {
        $command = new IncreaseCountInStatisticsCompanyByTypeCommand($this->company, StatisticsType::phoneViews());

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testCommandFilledWithInByNonExistentCompanyShouldCauseErrors(): void
    {
        $nonExistentCompany = new Company(
            Uuid::uuid4(),
            $this->getFaker()->realText(20),
            $this->getFaker()->realText(20),
            $this->getFaker()->realText(20),
            $this->getFaker()->realText(20),
            new ArrayCollection([$this->rubric])
        );

        $command = new IncreaseCountInStatisticsCompanyByTypeCommand($nonExistentCompany, StatisticsType::phoneViews());

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('company', 'Компания не найдена.');
    }

    public function testCommandFilledWithInByInvalidStatisticsTypeShouldCauseErrors(): void
    {
        $invalidStatisticType = 'InvalidType';
        $command = new IncreaseCountInStatisticsCompanyByTypeCommand($this->company, $invalidStatisticType);
        $expectedMessage = 'Недопустимый тип статистики.';

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('statisticsType', $expectedMessage);
    }
}
