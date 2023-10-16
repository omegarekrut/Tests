<?php

namespace Tests\Controller\WeeklyLetter;

use Symfony\Component\DomCrawler\Crawler;
use Tests\Controller\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterBefore;
use Tests\DataFixtures\ORM\WeeklyLetter\LoadWeeklyLetterCurrent;

/**
 * @group weekly-letter
 */
class WeeklyLetterControllerTest extends TestCase
{
    private const WEEKLY_LETTER_NUMBER = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();
        $this->browser = $this->getBrowser();
    }

    public function testSeeWeeklyLetterPageHeadersAndLinksUtmParams(): void
    {
        $this->loadFixtures([
            LoadWeeklyLetterBefore::class
        ]);

        $this->browser->request('GET', sprintf('/weekly-letter/view/%s/', self::WEEKLY_LETTER_NUMBER));

        $this->assertEquals(Response::HTTP_OK, $this->browser->getResponse()->getStatusCode());

        $crawler = $this->browser->getCrawler();

        $this->assertStringContainsString('Рассылка №1. Самое интересное за неделю', $crawler->filter('h1')->text());
        $this->assertStringContainsString('Новости', $crawler->filter('.articles__list')->text());
        $this->assertStringContainsString('Новое на форуме', $crawler->filter('.articles__list')->text());

        $linkHrefs = $crawler->filter('a')->each(function (Crawler $node): ?string {
            return $node->attr('href');
        });

        foreach ($linkHrefs as $linkHref) {
            $this->assertStringContainsString('utm_source=', $linkHref);
            $this->assertStringContainsString('utm_medium=', $linkHref);
            $this->assertStringContainsString('utm_campaign=', $linkHref);
        }
    }
}
