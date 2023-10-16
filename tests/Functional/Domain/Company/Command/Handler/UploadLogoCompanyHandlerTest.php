<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Command\UploadLogoCompanyCommand;
use App\Domain\Company\Entity\Company;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\Functional\TestCase;

class UploadLogoCompanyHandlerTest extends TestCase
{
    private Company $company;
    private UploadLogoCompanyCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $this->command = new UploadLogoCompanyCommand($this->company);

        $this->command->croppableImage->imageFile = new UploadedFile(
            sprintf('%s/image20x29.jpeg', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            null,
            100,
            0,
            true
        );
        $this->command->croppableImage->sourceImageWidth = 20;
        $this->command->croppableImage->rotationAngle = 0;
        $this->command->croppableImage->croppingParameters = ['x0' => 0, 'y0' => 0, 'x1' => 10, 'y1' => 10];
    }

    protected function tearDown(): void
    {
        unset(
            $this->company,
            $this->imageStorageClient,
            $this->command
        );

        parent::tearDown();
    }

    public function testCompanyAfterHandlingMustGetNewLogo(): void
    {
        $oldCompanyLogoImage = $this->company->getLogoImage();

        $this->getCommandBus()->handle($this->command);

        $this->assertNotEmpty($this->company->getLogoImage());
        $this->assertFalse($oldCompanyLogoImage === $this->company->getLogoImage());
    }
}
