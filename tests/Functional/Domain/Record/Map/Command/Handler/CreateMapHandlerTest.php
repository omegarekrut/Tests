<?php

namespace Tests\Functional\Domain\Record\Map\Command\Handler;

use App\Domain\Record\Map\Command\CreateMapCommand;
use App\Domain\Record\Map\Entity\Map;
use App\Domain\Region\Entity\Region;
use App\Domain\User\Entity\User;
use App\Util\Coordinates\Coordinates;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\Functional\TestCase;

class CreateMapHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadTestRegion::class,
        ])->getReferenceRepository();

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $userAdmin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($userAdmin instanceof User);

        $command = new CreateMapCommand(new Coordinates(10.0, 11.1), $userAdmin);
        $command->title = 'title';
        $command->description = 'desc';

        $this->getCommandBus()->handle($command);

        $mapRepository = $this->getEntityManager()->getRepository(Map::class);

        /** @var Map $map */
        $map = $mapRepository->findLastMapForUser($userAdmin);

        $this->assertEquals($map->getRegion(), $region);
    }
}
