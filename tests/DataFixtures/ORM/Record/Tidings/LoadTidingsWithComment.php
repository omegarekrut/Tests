<?php

namespace Tests\DataFixtures\ORM\Record\Tidings;

use App\Domain\Record\Tidings\Entity\Tidings;
use App\Module\Author\AuthorInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadTidingsWithComment extends Fixture implements DependentFixtureInterface, SingleReferenceFixtureInterface
{
    private Generator $generator;
    private MediaHelper $mediaHelper;

    public static function getReferenceName(): string
    {
        return 'tidings-with-comment';
    }

    public function __construct(Generator $generator, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $tidingsAuthor = $this->getReference(LoadTestUser::getReferenceName());
        assert($tidingsAuthor instanceof AuthorInterface);

        $tidings = new Tidings(
            $this->generator->realText(20),
            $this->generator->realText(100),
            $tidingsAuthor
        );

        $tidings->addImage($this->mediaHelper->createImage());
        RatingHelper::setRating($tidings);

        $manager->persist($tidings);

        $commentAuthor = $this->getReference(LoadMostActiveUser::getReferenceName());
        assert($commentAuthor instanceof AuthorInterface);

        $tidings->addComment(
            Uuid::uuid4(),
            $this->generator->regexify('[A-Za-z0-9]{20}'),
            $this->generator->realText(),
            $commentAuthor,
        );

        $manager->flush();

        $this->addReference(static::getReferenceName(), $tidings);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadMostActiveUser::class,
            LoadTestUser::class,
            LoadTestRegion::class,
        ];
    }
}
