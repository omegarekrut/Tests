<?php

namespace Tests\Functional\Domain\Company\Repository;

use App\Domain\Company\Repository\RubricRepository;
use Tests\Functional\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\Company\LoadPaidReservoirsCompany;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsCompany;

/**
 * @group company
 * @group company-create
 */
class RubricRepositoryTest extends TestCase
{
    private ?RubricRepository $rubricRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rubricRepository = $this->getContainer()->get(RubricRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->rubricRepository);

        parent::tearDown();
    }

    public function testCheckingRubricsOrderInDescendingOrder(): void
    {
        $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadPaidReservoirsCompany::class,
            LoadTackleShopsCompany::class,
        ]);

        $rubrics = $this->rubricRepository->findAll();

        $lastPriorityValue = null;

        foreach ($rubrics as $rubric) {
            if ($lastPriorityValue !== null) {
                $this->assertLessThan($lastPriorityValue, $rubric->getPriority());
            }

            $lastPriorityValue = $rubric->getPriority();
        }
    }
}
