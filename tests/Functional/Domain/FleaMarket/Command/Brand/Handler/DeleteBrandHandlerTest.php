<?php

namespace Tests\Functional\Domain\FleaMarket\Command\Brand\Handler;

use App\Domain\FleaMarket\Command\Brand\DeleteBrandCommand;
use App\Domain\FleaMarket\Repository\FleaMarketBrandRepository;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\FleaMarket\LoadFleaMarketBrandWithoutLogo;
use Tests\Functional\TestCase;

class DeleteBrandHandlerTest extends TestCase
{
    private FleaMarketBrandRepository $brandRepository;
    private ReferenceRepository $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandRepository = $this->getContainer()->get(FleaMarketBrandRepository::class);

        $this->referenceRepository = $this->loadFixtures([
            LoadFleaMarketBrandWithoutLogo::class,
        ])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset(
            $this->brandRepository,
            $this->referenceRepository
        );

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $brandToDelete = $this->referenceRepository->getReference(LoadFleaMarketBrandWithoutLogo::REFERENCE_NAME);

        $deleteBrandCommand = new DeleteBrandCommand($brandToDelete);
        $this->getCommandBus()->handle($deleteBrandCommand);

        $deletedBrands = $this->brandRepository->findById($brandToDelete->getId());

        $this->assertEmpty($deletedBrands);
    }
}
