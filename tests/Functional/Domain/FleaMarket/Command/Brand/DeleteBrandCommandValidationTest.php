<?php

namespace Tests\Functional\Domain\FleaMarket\Command\Brand;

use App\Domain\FleaMarket\Command\Brand\DeleteBrandCommand;
use App\Domain\FleaMarket\Entity\Brand;
use Tests\DataFixtures\ORM\FleaMarket\LoadFleaMarketBrandWithoutLogo;
use Tests\Functional\ValidationTestCase;

class DeleteBrandCommandValidationTest extends ValidationTestCase
{
    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadFleaMarketBrandWithoutLogo::class,
        ])->getReferenceRepository();

        $this->brand = $referenceRepository->getReference(LoadFleaMarketBrandWithoutLogo::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->brand
        );

        parent::tearDown();
    }

    public function testValidationPassed(): void
    {
        $command = new DeleteBrandCommand($this->brand);

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
