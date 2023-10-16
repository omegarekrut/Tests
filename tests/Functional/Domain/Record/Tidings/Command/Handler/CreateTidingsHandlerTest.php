<?php

namespace Tests\Functional\Domain\Record\Tidings\Command\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\Tidings\Command\CreateTidingsCommand;
use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Tidings\Repository\TidingsRepository;
use App\Domain\Record\Tidings\ValueObject\FishingMethodCollection;
use App\Domain\Region\Entity\Region;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\ImageWithRotationAngle;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use DateTime;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

class CreateTidingsHandlerTest extends TestCase
{
    private User $user;
    private TidingsRepository $tidingsRepository;
    private Region $region;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadNovosibirskRegion::class,
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $this->tidingsRepository = $this->getEntityManager()->getRepository(Tidings::class);
        $this->region = $referenceRepository->getReference(LoadNovosibirskRegion::REFERENCE_NAME);
        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->tidingsRepository,
            $this->region,
            $this->company
        );

        parent::tearDown();
    }

    public function testTidingsIsCreatedAndSaved(): void
    {
        $command = new CreateTidingsCommand($this->user);
        $command->title = 'Tidings title';
        $command->text = 'Tidings text';
        $command->images = new ImageWithRotationAngleCollection([]);
        $command->regionId = $this->region->getId();

        $this->getCommandBus()->handle($command);

        $actualTidings = $this->tidingsRepository->findLastTidingForUser($this->user);
        assert($actualTidings instanceof Tidings);

        $this->assertEquals($command->title, $actualTidings->getTitle());
        $this->assertEquals($command->text, $actualTidings->getText());
        $this->assertEquals(new ImageCollection([]), $actualTidings->getImages());
        $this->assertEquals($command->regionId, $actualTidings->getRegion()->getId());
    }

    public function testTidingsIsCreatedAndSavedByCompanyAuthor(): void
    {
        $command = new CreateTidingsCommand($this->user);
        $command->title = 'Tidings title';
        $command->text = 'Tidings text';
        $command->images = new ImageWithRotationAngleCollection([]);
        $command->author = $this->company->getOwner();
        $command->companyAuthor = $this->company;
        $command->regionId = $this->region->getId();

        $this->getCommandBus()->handle($command);

        $actualTidings = $this->tidingsRepository->findLastTidingForUser($command->author);
        assert($actualTidings instanceof Tidings);

        $this->assertEquals($command->companyAuthor, $actualTidings->getCompanyAuthor());
        $this->assertEquals($command->companyAuthor->getName(), $actualTidings->getCompanyAuthorName());
    }

    public function testCreateTidingWithoutRegion(): void
    {
        $command = new CreateTidingsCommand($this->user);
        $command->title = 'Tidings title';
        $command->text = 'Tidings text';
        $command->images = new ImageWithRotationAngleCollection([]);
        $command->regionId = Region::OTHER_REGION_ID;

        $this->getCommandBus()->handle($command);

        $actualTidings = $this->tidingsRepository->findLastTidingForUser($this->user);
        assert($actualTidings instanceof Tidings);

        $this->assertEquals($command->title, $actualTidings->getTitle());
        $this->assertEquals($command->text, $actualTidings->getText());
        $this->assertEquals(new ImageCollection([]), $actualTidings->getImages());
        $this->assertNull($actualTidings->getRegion());
    }

    public function testTidingsWithFishingDiaryIsCreatedAndSaved(): void
    {
        $command = new CreateTidingsCommand($this->user);
        $command->title = 'Tidings title';
        $command->text = 'Tidings text';
        $command->fishingTime = 'Tidings fishing time';
        $command->place = 'Tidings map point';
        $command->tackles = 'Tidings tackles';
        $command->catch = 'Tidings catch';
        $command->weather = 'Tidings weather';
        $command->startDate = $this->getFaker()->dateTime();
        $command->endDate = new DateTime();
        $command->fishingMethods = ['спиннинг', 'поплавочная удочка'];
        $command->images = new ImageWithRotationAngleCollection([new ImageWithRotationAngle('filename.jpg', 90)]);
        $command->regionId = $this->region->getId();

        $this->getCommandBus()->handle($command);

        $actualTidings = $this->tidingsRepository->findLastTidingForUser($this->user);
        assert($actualTidings instanceof Tidings);

        $this->assertEquals($command->title, $actualTidings->getTitle());
        $this->assertEquals($command->text, $actualTidings->getText());
        $this->assertEquals($command->startDate, $actualTidings->getDateStart());
        $this->assertEquals($command->endDate, $actualTidings->getDateEnd());
        $this->assertEquals($command->place, $actualTidings->getPlace());
        $this->assertEquals(false, $actualTidings->isHiddenPlace());
        $this->assertEquals($command->tackles, $actualTidings->getTackles());
        $this->assertEquals($command->catch, $actualTidings->getCatch());
        $this->assertEquals($command->weather, $actualTidings->getWeather());
        $this->assertEquals($command->fishingTime, $actualTidings->getFishingTime());
        $this->assertEquals(new FishingMethodCollection($command->fishingMethods), $actualTidings->getFishingMethods());
        $this->assertEquals(new ImageCollection([ new Image('transformer image name.jpg')]), $actualTidings->getImages());
    }
}
