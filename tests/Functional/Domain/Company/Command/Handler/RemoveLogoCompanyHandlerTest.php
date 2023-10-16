<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Command\RemoveLogoCompanyCommand;
use App\Domain\Company\Entity\Company;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageStorageClientInterface;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\Functional\TestCase;

class RemoveLogoCompanyHandlerTest extends TestCase
{
    private ?ImageStorageClientInterface $imageStorageClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageStorageClient = $this->getContainer()->get(ImageStorageClientInterface::class);
    }

    protected function tearDown(): void
    {
        unset($this->imageStorageClient);

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        /** @var Company $company */
        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);

        $companyLogoImage = $company->getLogoImage();

        $this->assertNotNull($companyLogoImage);

        $command = new RemoveLogoCompanyCommand($company);
        $this->getCommandBus()->handle($command);

        $this->assertThatImageDeletedFromStorage($companyLogoImage->getImage());
        $this->assertNull($company->getLogoImage());
    }

    private function assertThatImageDeletedFromStorage(Image $image): void
    {
        $deletedImages = $this->imageStorageClient->getDeletedImagesAndClear();
        $deletedImage = current($deletedImages);

        $this->assertEquals($image, $deletedImage);
    }
}
