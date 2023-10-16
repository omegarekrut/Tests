<?php

namespace Tests\DataFixtures\ORM\Record\Tidings;

use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;

class LoadSimpleTidings extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'simple-tidings';

    private Generator $generator;
    private MediaHelper $mediaHelper;

    public function __construct(Generator $generator, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $author = $this->getReference(LoadMostActiveUser::USER_MOST_ACTIVE);
        assert($author instanceof User);

        $tidings = new Tidings(
            $this->generator->realText(20),
            $this->generator->realText(100),
            $author
        );

        $tidings->addImage($this->mediaHelper->createImage());
        RatingHelper::setRating($tidings);

        $manager->persist($tidings);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $tidings);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadMostActiveUser::class,
            LoadTestRegion::class,
        ];
    }
}
