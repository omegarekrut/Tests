<?php

namespace Tests\Functional\Domain\Record\Video\Command;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Video\Command\UpdateVideoCommand;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\Region\Entity\Region;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\Functional\ValidationTestCase;

/**
 * @group video
 */
class UpdateVideoCommandValidationTest extends ValidationTestCase
{
    private UpdateVideoCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadVideos::class,
            LoadTestRegion::class,
        ])->getReferenceRepository();

        $video = $referenceRepository->getReference(LoadVideos::getRandReferenceName());
        assert($video instanceof Video);

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $this->command = new UpdateVideoCommand($video);
        $this->command->category = $this->createMock(Category::class);
        $this->command->title = 'title';
        $this->command->description = 'text';
        $this->command->regionId = $region->getId();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['category', 'title', 'description'], null, 'Это поле обязательно для заполнения.');
    }

    public function testInvalidLength(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title'], $this->getFaker()->realText(500), 'Длина не должна превышать 255 символов.');
    }

    public function testPreviewContainTooMuchUpperCase(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'description'], mb_strtoupper($this->getFaker()->realText(50)), 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testInvalidRegionId(): void
    {
        $this->command->regionId = Uuid::uuid4()->toString();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('regionId', 'Регион места съемки не найден.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
