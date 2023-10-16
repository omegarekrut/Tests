<?php

namespace Tests\DataFixtures\ORM\CompanyLetter;

use App\Domain\CompanyLetter\Entity\GreetingBlock;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class LoadGreetingBlockPreviousMonth extends Fixture implements FixtureInterface
{
    public const REFERENCE_NAME = 'load-company-greeting-block-previous-month';
    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $id = Uuid::uuid4();
        $data = $this->generator->realText(500);
        $startAt = Carbon::today()->subMonth()->startOfMonth();
        $finishAt = Carbon::today()->subMonths()->endOfMonth();

        $greetingBlock = new GreetingBlock(
            $id,
            $data,
            $startAt,
            $finishAt
        );
        $this->addReference(self::REFERENCE_NAME, $greetingBlock);

        $manager->persist($greetingBlock);
        $manager->flush();
    }
}
