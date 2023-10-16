<?php

namespace Tests\DataFixtures\ORM;

use App\Domain\FleaMarket\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator as FakerGenerator;
use Ramsey\Uuid\Uuid;

class LoadFleaMarketCategories extends Fixture
{
    public const ROOT_ARTICLES = 'article';
    public const ROOT_SNASTI_ARTICLES = 'snasti_article';
    public const ROOT_GALLERY = 'gallery';
    public const ROOT_VIDEO = 'video';
    public const ROOT_TACKLE = 'tackles';
    public const ROOT_TACKLE_REVIEWS = 'tackle_reviews';

    public const REFERENCE_ROOT_ARTICLE_TACKLE = 'flea-market-category-articles-child-2';

    /**
     * @var string[][]|int[][] $format [$slug => [$title, $childrenCount]]
     */
    private static $rootImportantCategoryConfig = [
        self::ROOT_ARTICLES => ['Записи', 9],
        self::ROOT_GALLERY => ['Рыболовная фотогалерея', 10],
        self::ROOT_VIDEO => ['Видео о рыбалке', 10],
        self::ROOT_TACKLE => ['Снасти', 10],
        self::ROOT_TACKLE_REVIEWS => ['Отзывы о снастях', 0],
    ];

    private FakerGenerator $generator;

    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    public static function getReferenceRootName(string $rootCategory): string
    {
        return sprintf('flea-market-category-%s', $rootCategory);
    }

    public static function getRandChildReferenceNameByRootCategory(string $rootCategory): string
    {
        $maxId = self::getCountChildForRoot($rootCategory);

        if ($rootCategory === self::ROOT_ARTICLES) {
            $maxId++;
        }

        return sprintf('%s-child-%d', self::getReferenceRootName($rootCategory), random_int(1, $maxId));
    }

    private static function getCountChildForRoot(string $rootCategory): int
    {
        $information = self::$rootImportantCategoryConfig[$rootCategory] ?? [];

        return (int) ($information[count($information) - 1] ?? 0);
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::$rootImportantCategoryConfig as $slug => $rootCategoryConfig) {
            [$title, $childrenCount] = $rootCategoryConfig;
            $rootCategory = new Category(Uuid::uuid4(), $title, $slug);

            $manager->persist($rootCategory);
            $rootReferenceName = self::getReferenceRootName($slug);
            $this->addReference($rootReferenceName, $rootCategory);

            for ($i = 1; $i <= $childrenCount; $i++) {
                $child = new Category(
                    Uuid::uuid4(),
                    $this->generator->realText(20),
                    $this->generator->unique()->slug(1, false),
                    $rootCategory
                );

                $manager->persist($child);

                $this->addReference(sprintf('%s-child-%d', $rootReferenceName, $i), $child);
            }
        }

        $manager->flush();

        $this->generator->unique(true);

        $this->createTacklesCategory($manager);
    }

    /**
     * Required child category of articles
     */
    private function createTacklesCategory(ObjectManager $manager): void
    {
        $articleSubCategory = new Category(
            Uuid::uuid4(),
            $this->generator->realText(20),
            self::ROOT_SNASTI_ARTICLES,
            $this->getReference(self::getReferenceRootName(self::ROOT_ARTICLES))
        );

        $manager->persist($articleSubCategory);
        $manager->flush();

        $this->addReference(self::REFERENCE_ROOT_ARTICLE_TACKLE, $articleSubCategory);
    }
}
