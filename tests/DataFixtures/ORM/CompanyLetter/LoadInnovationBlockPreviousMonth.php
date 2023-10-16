<?php

namespace Tests\DataFixtures\ORM\CompanyLetter;

use App\Domain\CompanyLetter\Entity\InnovationBlock;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\MediaHelper;

class LoadInnovationBlockPreviousMonth extends Fixture
{
    public const REFERENCE_NAME = 'load-innovation-blocks-previous-month';

    private Generator $generator;
    private MediaHelper $mediaHelper;


    public function __construct(Generator $generator, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $startAt = Carbon::today()->subMonth()->startOfMonth();
        $finishAt = Carbon::today()->subMonths()->endOfMonth();

        $innovationBlock = new InnovationBlock(
            Uuid::uuid4(),
            $this->generator->text(50),
            $this->generator->text(100),
            $this->mediaHelper->createImage(),
            $startAt,
            $finishAt
        );

        $manager->persist($innovationBlock);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $innovationBlock);
    }
}
