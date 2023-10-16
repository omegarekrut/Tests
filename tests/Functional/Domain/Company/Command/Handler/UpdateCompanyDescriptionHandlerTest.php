<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Command\UpdateCompanyDescriptionCommandFactory;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Media\CompanyImage;
use App\Domain\Company\Entity\Media\CompanyYoutubeVideo;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\Functional\TestCase;
use App\Util\ImageStorage\ImageWithRotationAngle;

/**
 * @group update-company
 */
class UpdateCompanyDescriptionHandlerTest extends TestCase
{
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->company);

        parent::tearDown();
    }

    public function testUpdateDescriptionHandle(): void
    {
        $expectedDescription = 'Новое описание компании';

        $command = UpdateCompanyDescriptionCommandFactory::create($this->company);
        $command->description = $expectedDescription;
        $command->images = new ArrayCollection([]);

        $this->getCommandBus()->handle($command);

        $this->assertEquals($expectedDescription, $this->company->getDescription());
    }

    public function testUpdateImagesHandle(): void
    {
        $expectedImage = 'image.jpg';
        $imageWithRotationAngle = new ImageWithRotationAngle($expectedImage, 0);

        $command = UpdateCompanyDescriptionCommandFactory::create($this->company);
        $command->images = new ArrayCollection([$imageWithRotationAngle]);

        $this->getCommandBus()->handle($command);

        $this->assertEquals(
            new ArrayCollection([$expectedImage]),
            $this->company->getImages()
            ->map(static function (CompanyImage $companyImage) {
                return $companyImage->getImage();
            })
        );
    }

    public function testUpdateImagesToEmptyHandle(): void
    {
        $command = UpdateCompanyDescriptionCommandFactory::create($this->company);
        $command->images = $expectedImages = new ArrayCollection([]);

        $this->getCommandBus()->handle($command);

        $this->assertEquals(
            $expectedImages,
            $this->company->getImages()
                ->map(static function (CompanyImage $companyImage) {
                    return $companyImage->getImage();
                })
        );
    }

    public function testUpdateVideoUrlHandle(): void
    {
        $command = UpdateCompanyDescriptionCommandFactory::create($this->company);

        $command->videoUrls = $expectedVideoUrls = ['https://www.youtube.com/watch?v=-964sSBviK0&ab_channel=Jamie%27sDesign'];
        $command->images = new ArrayCollection([]);

        $this->getCommandBus()->handle($command);

        $this->assertEquals(
            $expectedVideoUrls,
            array_map(fn (CompanyYoutubeVideo $videoObject) => $videoObject->getVideoUrl(), $this->company->getVideos()->toArray())
        );
    }

    public function testUpdateToEmptyVideoUrlHandle(): void
    {
        $command = UpdateCompanyDescriptionCommandFactory::create($this->company);

        $command->videoUrls = $expectedVideoUrls = [];
        $command->images = new ArrayCollection([]);

        $this->getCommandBus()->handle($command);

        $this->assertEquals(
            $expectedVideoUrls,
            array_map(fn (CompanyYoutubeVideo $videoObject) => $videoObject->getVideoUrl(), $this->company->getVideos()->toArray())
        );
    }

    public function testResetDescriptionToEmptyStringHandle(): void
    {
        $expectedDescription = '';

        $command = UpdateCompanyDescriptionCommandFactory::create($this->company);
        $command->description = $expectedDescription;
        $command->images = new ArrayCollection([]);

        $this->getCommandBus()->handle($command);

        $this->assertNull($this->company->getDescription());
    }

    public function testResetDescriptionToNullHandle(): void
    {
        $expectedDescription = null;

        $command = UpdateCompanyDescriptionCommandFactory::create($this->company);
        $command->description = $expectedDescription;
        $command->images = new ArrayCollection([]);

        $this->getCommandBus()->handle($command);

        $this->assertNull($this->company->getDescription());
    }
}
