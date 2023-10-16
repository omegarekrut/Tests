<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\Record\Common\Entity\Exception\WrongWayChangeRssExportStatusException;
use App\Domain\Record\Tackle\Entity\Tackle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\TackleFakeFactory;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadTackles extends Fixture implements DependentFixtureInterface
{
    private const REFERENCE_PREFIX = 'tackle';
    private const COUNT = 30;

    private TackleFakeFactory $tackleFakeFactory;

    public function __construct(TackleFakeFactory $tackleFakeFactory)
    {
        $this->tackleFakeFactory = $tackleFakeFactory;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', self::REFERENCE_PREFIX, rand(1, self::COUNT));
    }

    /**
     * @throws WrongWayChangeRssExportStatusException
     */
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $tackle = $this->tackleFakeFactory->createFake($this);
            $this->setRandomRssExportStatus($tackle);

            RatingHelper::setRating($tackle);

            $manager->persist($tackle);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $tackle);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadTackleBrands::class,
            LoadCategories::class,
            LoadMostActiveUser::class,
            LoadNumberedUsers::class,
        ];
    }

    /**
     * @throws WrongWayChangeRssExportStatusException
     * @throws \Exception
     */
    private function setRandomRssExportStatus(Tackle $tackle): void
    {
        $randomInt = random_int(1, 10) % 4;

        switch ($randomInt) {
            case 0:
                $tackle->markRssExportStatusAsAllowed();
                break;
            case 1:
                $tackle->markRssExportStatusAsDisallowed();
                break;
            case 2:
                $tackle
                    ->markRssExportStatusAsAllowed()
                    ->markRssExportStatusAsPublished();
                break;
        }
    }
}
