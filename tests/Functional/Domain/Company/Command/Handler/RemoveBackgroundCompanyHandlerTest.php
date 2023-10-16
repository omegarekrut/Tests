<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Command\RemoveBackgroundCompanyCommand;
use App\Domain\Company\Entity\Company;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageStorageClientInterface;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\Functional\TestCase;

class RemoveBackgroundCompanyHandlerTest extends TestCase
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

        $companyBackgroundImage = $company->getBackgroundImage();

        $this->assertNotNull($companyBackgroundImage);

        $command = new RemoveBackgroundCompanyCommand($company);
        $this->getCommandBus()->handle($command);

        $this->assertThatImageDeletedFromStorage($companyBackgroundImage->getImage());
        $this->assertNull($company->getBackgroundImage());
    }

    private function assertThatImageDeletedFromStorage(Image $image): void
    {
        $deletedImages = $this->imageStorageClient->getDeletedImagesAndClear();
        $deletedImage = current($deletedImages);

        $this->assertEquals($image, $deletedImage);
    }
}
