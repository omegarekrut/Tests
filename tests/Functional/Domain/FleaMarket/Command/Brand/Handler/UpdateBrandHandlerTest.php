<?php

namespace Tests\Functional\Domain\FleaMarket\Command\Brand\Handler;

use App\Domain\FleaMarket\Command\Brand\UpdateBrandCommand;
use App\Domain\FleaMarket\Entity\Brand;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\DataFixtures\ORM\FleaMarket\LoadFleaMarketBrandWithoutLogo;
use Tests\Functional\TestCase;

class UpdateBrandHandlerTest extends TestCase
{
    private ReferenceRepository $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadFleaMarketBrandWithoutLogo::class,
        ])->getReferenceRepository();
    }

    public function testHandle(): void
    {
        $brandToUpdate = $this->referenceRepository->getReference(LoadFleaMarketBrandWithoutLogo::REFERENCE_NAME);
        assert($brandToUpdate instanceof Brand);

        $command = new UpdateBrandCommand($brandToUpdate);

        $command->title = 'Новое название бренда';
        $command->description = 'Новое описание бренда';
        $command->logoImage = $this->createLogoImage();

        $this->getCommandBus()->handle($command);
        $updatedBrand = $brandToUpdate;

        $this->assertEquals($command->title, $updatedBrand->getTitle());
        $this->assertEquals($command->description, $updatedBrand->getDescription());
        $this->assertEquals($command->logoImage->getClientOriginalName(), $updatedBrand->getLogoImage()->getFileName());
    }

    public function testHandleCommandWithoutImage(): void
    {
        $brand = $this->referenceRepository->getReference(LoadFleaMarketBrandWithoutLogo::REFERENCE_NAME);
        assert($brand instanceof Brand);

        $command = new UpdateBrandCommand($brand);

        $command->title = 'Новое название бренда';
        $command->description = 'Новое описание бренда';

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->title, $brand->getTitle());
        $this->assertEquals($command->description, $brand->getDescription());
        $this->assertNull($brand->getLogoImage()->getFileName());
    }

    private function createLogoImage(): UploadedFile
    {
        return new UploadedFile(
            sprintf('%s/image40x57.jpeg', $this->getDataFixturesFolder()),
            'image40x57.jpeg',
            null,
            100,
            0,
            true
        );
    }
}
