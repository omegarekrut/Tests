<?php

namespace Tests\Functional\Domain\Record\Video\Command;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Video\Command\UpdateVideoInAdminCommand;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\Region\Entity\Country;
use App\Domain\Region\Entity\Region;
use App\Util\ImageStorage\Image;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\Functional\ValidationTestCase;

/**
 * @group video
 */
class UpdateVideoInAdminCommandValidationTest extends ValidationTestCase
{
    private UpdateVideoInAdminCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestRegion::class,
            LoadVideos::class,
        ])->getReferenceRepository();

        $video = $referenceRepository->getReference(LoadVideos::getRandReferenceName());
        assert($video instanceof Video);

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $this->command = new UpdateVideoInAdminCommand($video);
        $this->command->category = $this->createMock(Category::class);
        $this->command->videoUrl = 'videoUrl';
        $this->command->title = 'title';
        $this->command->description = 'text';
        $this->command->iframe = 'iframe';
        $this->command->image = $this->createMock(Image::class);
        $this->command->region = $region;
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['category', 'title', 'description', 'iframe', 'image'], null, 'Это поле обязательно для заполнения.');
    }

    public function testPreviewContainTooMuchUpperCase(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'description'], mb_strtoupper($this->getFaker()->realText(50)), 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testInvalidLengthFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов.');
    }

    public function testInvalidRegionId(): void
    {
        $region = new Region(Uuid::uuid4(), $this->createMock(Country::class), 'test', 'test', 'test', 'test');
        $this->command->region = $region;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('region', 'Регион для вести не найден.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
