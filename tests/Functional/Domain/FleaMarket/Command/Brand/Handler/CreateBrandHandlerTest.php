<?php

namespace Tests\Functional\Domain\FleaMarket\Command\Brand\Handler;

use App\Domain\FleaMarket\Command\Brand\CreateBrandCommand;
use App\Domain\FleaMarket\Repository\FleaMarketBrandRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Functional\TestCase;

class CreateBrandHandlerTest extends TestCase
{
    public function testCreateBrandWithLogo(): void
    {
        $command = new CreateBrandCommand();

        $command->title = 'Тестовый бренд';
        $command->description = 'Тестовое описание';
        $command->logoImage = $this->createLogoImage();

        $this->getCommandBus()->handle($command);

        $brandRepository = $this->getContainer()->get(FleaMarketBrandRepository::class);
        assert($brandRepository instanceof FleaMarketBrandRepository);

        $brand = $brandRepository->findById($command->id);

        $this->assertNotEmpty($brand);

        $this->assertEquals($command->title, $brand->getTitle());
        $this->assertEquals($command->description, $brand->getDescription());
        $this->assertEquals($command->logoImage->getClientOriginalName(), $brand->getLogoImage()->getFileName());
    }

    public function testCreateBrandWithoutLogo(): void
    {
        $command = new CreateBrandCommand();

        $command->title = 'Тестовый бренд без лого';
        $command->description = 'Тестовое описание';

        $this->getCommandBus()->handle($command);

        $brandRepository = $this->getContainer()->get(FleaMarketBrandRepository::class);
        assert($brandRepository instanceof FleaMarketBrandRepository);

        $brand = $brandRepository->findById($command->id);

        $this->assertEquals($command->title, $brand->getTitle());
        $this->assertEquals($command->description, $brand->getDescription());
        $this->assertNull($brand->getLogoImage()->getImage());
    }

    private function createLogoImage(): UploadedFile
    {
        return new UploadedFile(
            sprintf('%s/image20x29.jpeg', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            null,
            100,
            0,
            true
        );
    }
}
