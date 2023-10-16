<?php

namespace Tests\Functional\Domain\Record\Tidings\Command\Handler;

use App\Domain\Record\Tidings\Command\UpdateTidingsCommand;
use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Tidings\Repository\TidingsRepository;
use App\Domain\Record\Tidings\ValueObject\FishingMethodCollection;
use App\Domain\Region\Entity\Region;
use App\Module\YoutubeVideo\Collection\YoutubeVideoUrlCollection;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageWithRotationAngle;
use Carbon\Carbon;
use DateTime;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsWithRegion;
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;
use Tests\Functional\TestCase;

class UpdateTidingsHandlerTest extends TestCase
{
    private Tidings $tiding;
    private TidingsRepository $tidingsRepository;
    private Region $region;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTidingsWithRegion::class,
            LoadNovosibirskRegion::class,
        ])->getReferenceRepository();

        $this->tiding = $referenceRepository->getReference(LoadTidingsWithRegion::REFERENCE_NAME);
        $this->tidingsRepository = $this->getEntityManager()->getRepository(Tidings::class);
        $this->region = $referenceRepository->getReference(LoadNovosibirskRegion::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->tiding,
            $this->tidingsRepository,
            $this->region
        );

        parent::tearDown();
    }

    public function testSuccessHandleCommandWithoutChanges(): void
    {
        $command = new UpdateTidingsCommand($this->tiding);

        $now = Carbon::create();
        Carbon::setTestNow($now);

        try {
            $this->getCommandBus()->handle($command);

            $tidingsList = $this->tidingsRepository->findAllByTitle($command->title);

            $this->assertCount(1, $tidingsList);

            $tidings = current($tidingsList);
            assert($tidings instanceof Tidings);

            $this->assertEquals($now, $tidings->getUpdatedAt());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function testTidingsWithFishingDiaryIsUpdatedAndSaved(): void
    {
        $command = new UpdateTidingsCommand($this->tiding);
        $command->title = 'Tidings title'.$this->tiding->getTitle();
        $command->text = 'Tidings text'.$this->tiding->getText();
        $command->fishingTime = 'Tidings fishing time'.$this->tiding->getfishingTime();
        $command->place = 'Tidings map point'.$this->tiding->getPlace();
        $command->tackles = 'Tidings tackles'.$this->tiding->getTackles();
        $command->catch = 'Tidings catch'.$this->tiding->getCatch();
        $command->weather = 'Tidings weather'.$this->tiding->getWeather();
        $command->startDate = $this->getFaker()->dateTime();
        $command->endDate = new DateTime();
        $command->fishingMethods = ['спиннинг', 'поплавочная удочка', 'жерлицы', 'карпфишинг'];
        $command->images = new ImageWithRotationAngleCollection([new ImageWithRotationAngle('filename.jpg', 90)]);
        $command->videoUrls = [];
        $command->regionId = $this->region->getId();

        $this->assertNotEquals($command->regionId, $this->tiding->getRegion()->getId());

        $now = Carbon::create();
        Carbon::setTestNow($now);

        try {
            $this->getCommandBus()->handle($command);

            $tidingsList = $this->tidingsRepository->findAllByTitle($command->title);

            $this->assertCount(1, $tidingsList);

            $tidings = current($tidingsList);
            assert($tidings instanceof Tidings);

            $this->assertEquals($command->title, $tidings->getTitle());
            $this->assertEquals($command->text, $tidings->getText());
            $this->assertEquals($command->startDate, $tidings->getDateStart());
            $this->assertEquals($command->endDate, $tidings->getDateEnd());
            $this->assertEquals($command->place, $tidings->getPlace());
            $this->assertEquals(false, $tidings->isHiddenPlace());
            $this->assertEquals($command->tackles, $tidings->getTackles());
            $this->assertEquals($command->catch, $tidings->getCatch());
            $this->assertEquals($command->weather, $tidings->getWeather());
            $this->assertEquals($command->fishingTime, $tidings->getFishingTime());
            $this->assertEquals(new FishingMethodCollection($command->fishingMethods), $tidings->getFishingMethods());
            $this->assertEquals(new YoutubeVideoUrlCollection($command->videoUrls), $tidings->getVideoUrls());
            $this->assertEquals(new ImageCollection([new Image('transformer image name.jpg')]), $tidings->getImages());
            $this->assertEquals($now, $tidings->getUpdatedAt());
            $this->assertEquals($command->regionId, $tidings->getRegion()->getId());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function testResetRegionForTidingWithNoneRegionObject(): void
    {
        $this->assertNotEmpty($this->tiding->getRegion());

        $command = new UpdateTidingsCommand($this->tiding);
        $command->regionId = Region::OTHER_REGION_ID;

        $this->getCommandBus()->handle($command);

        $tidingFromRepository = $this->tidingsRepository->findById($this->tiding->getId());
        assert($tidingFromRepository instanceof Tidings);

        $this->assertNull($tidingFromRepository->getRegion());
    }
}
