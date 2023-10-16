<?php

namespace Tests\DataFixtures\ORM\Seo;

use App\Domain\Seo\Entity\SeoData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @todo refactor IMPORTANT_URI
 */
class LoadSeoData extends Fixture
{
    public const GALLERY = 'seo-data-gallery';
    public const TACKLE = 'seo-data-tackles';
    public const REGEX_PATTERN = 'seo-data-regex-pattern';
    public const HUMAN_PATTERN = 'seo-data-human-pattern';
    public const WITH_QUERY_STRING = 'seo-data-with-query-string';
    public const WITH_QUERY_STRING_AND_VALUE = 'seo-data-with-query-string-and-value';
    public const WITH_QUERY_STRING_AND_OTHER_VALUE = 'seo-data-with-query-string-and-other-value';

    private const IMPORTANT_URI = [
        self::GALLERY => [
            'uri' => '/gallery/',
            'title' => 'Title Рыболовная фотогалерея',
            'description' => 'Description Рыболовная фотогалерея',
            'h1' => 'H1 Рыболовная фотогалерея',
        ],
        self::TACKLE => [
            'uri' => '/tackles/',
            'title' => 'Отзывы о снастях',
            'description' => 'Отзывы о снастях',
            'h1' => 'Отзывы о снастях',
        ],
        self::HUMAN_PATTERN => [
            'uri' => '/articles/v*',
            'title' => 'Шаблон * Title `{title}`',
            'description' => 'Шаблон * Description `{description}`',
            'h1' => 'Шаблон * H1 `{h1}`, Month `{month}`, Year `{year}`',
        ],
        self::REGEX_PATTERN => [
            'uri' => '#/articles?/#siU',
            'title' => 'Шаблон # Title `{title}`',
            'description' => 'Шаблон # Description `{description}`',
            'h1' => 'Шаблон # H1 `{h1}`, Month `{month}`, Year `{year}`',
        ],
        self::WITH_QUERY_STRING => [
            'uri' => '/tackles/?*search*',
            'title' => 'Шаблон _GET & * Title `{title}`',
            'description' => 'Шаблон _GET & * Description `{description}`',
            'h1' => 'Шаблон _GET & *, Search `{search}`, Month `{month}`, Year `{year}`',
        ],
        self::WITH_QUERY_STRING_AND_VALUE => [
            'uri' => '/tackles/?*search=судак*',
            'title' => 'Шаблон _GET & * Title `{title}` ',
            'description' => 'Шаблон _GET & * Description `{description}`',
            'h1' => 'Шаблон _GET & *, Search `{search}`, Month `{month}`, Year `{year}`',
        ],
        self::WITH_QUERY_STRING_AND_OTHER_VALUE => [
            'uri' => '/tackles/?*search=судака*',
            'title' => 'Шаблон _GET & * Title `{title}` ',
            'description' => 'Шаблон _GET & * Description `{description}`',
            'h1' => 'Шаблон _GET & *, Search `{search}`, Month `{month}`, Year `{year}`',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::IMPORTANT_URI as $referenceName => $data) {
            $seoData = new SeoData(
                $data['uri'],
                $data['title'],
                $data['h1'],
                $data['description']
            );

            $manager->persist($seoData);
            $this->addReference($referenceName, $seoData);
        }

        $manager->flush();
    }
}
