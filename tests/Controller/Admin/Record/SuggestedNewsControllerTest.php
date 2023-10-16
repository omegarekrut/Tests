<?php

namespace Tests\Controller\Admin\Record;

use App\Domain\SuggestedNews\Entity\SuggestedNews;
use App\Domain\User\Entity\User;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\LoadSuggestedNews;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

class SuggestedNewsControllerTest extends TestCase
{
    private const SUGGESTED_NEWS_INDEX_PATH = '/admin/suggested-news/';

    private User $admin;

    private SuggestedNews $suggestedNews;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadSuggestedNews::class,
        ])->getReferenceRepository();

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);
        $this->admin = $admin;

        $suggestedNews = $referenceRepository->getReference(LoadSuggestedNews::getRandReferenceName());
        assert($suggestedNews instanceof SuggestedNews);
        $this->suggestedNews = $suggestedNews;
    }

    public function testAdminCanSeeSuggestedNewsList(): void
    {
        $browser = $this->getBrowser()->loginUser($this->admin);
        $viewPage = $browser->request('GET', self::SUGGESTED_NEWS_INDEX_PATH);

        $this->assertStringContainsString('Список предложенных новостей', $viewPage->filter('h1')->html());
    }

    public function testFilterSuggestedNewsList(): void
    {
        $browser = $this->getBrowser()->loginUser($this->admin);
        $viewPage = $browser->request('GET', self::SUGGESTED_NEWS_INDEX_PATH);

        $expectedSuggestedNewsTitle = $this->suggestedNews->getTitle();
        $unexpectedSuggestedNewsTitles = $this->getUnexpectedSuggestedNewsTitles($viewPage, $expectedSuggestedNewsTitle);
        assert(count($unexpectedSuggestedNewsTitles) > 0);

        $viewPage = $browser->submitForm('Поиск', [
            'suggested_news_search[title]' => $expectedSuggestedNewsTitle,
        ], 'GET');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString($expectedSuggestedNewsTitle, $viewPage->filter('table tbody')->html());

        foreach ($unexpectedSuggestedNewsTitles as $unexpectedSuggestedNewsTitle) {
            $this->assertStringNotContainsString($unexpectedSuggestedNewsTitle, $viewPage->filter('table tbody')->html());
        }
    }

    public function testResetFilterSuggestedNewsList(): void
    {
        $browser = $this->getBrowser()->loginUser($this->admin);
        $viewPage = $browser->request('GET', self::SUGGESTED_NEWS_INDEX_PATH);

        $unexpectedTitle = $this->suggestedNews->getTitle();
        $expectedTitles = $this->getUnexpectedSuggestedNewsTitles($viewPage, $unexpectedTitle);
        assert(count($expectedTitles) > 0);

        $viewPage = $browser->submitForm('Поиск', [
            'suggested_news_search[title]' => $unexpectedTitle,
        ], 'GET');


        $resetLink = $viewPage->selectLink('Сбросить')->link();
        $viewPage = $browser->click($resetLink);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        foreach ($expectedTitles as $unexpectedSuggestedNewsTitle) {
            $this->assertStringContainsString($unexpectedSuggestedNewsTitle, $viewPage->filter('table tbody')->html());
        }
    }

    public function testDeleteSuggestedNews(): void
    {
        $browser = $this->getBrowser()->loginUser($this->admin);
        $viewPage = $browser->request('GET', self::SUGGESTED_NEWS_INDEX_PATH);

        $deletePath = $viewPage->filter('tbody span[data-js-action="ajax-delete"]')->attr('data-js-target');

        $browser->request('GET', $deletePath);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertEquals('{"status":"ok"}', $this->getBrowser()->getResponse()->getContent());
    }

    /**
     * @return string[]
     */
    private function getUnexpectedSuggestedNewsTitles(Crawler $viewPage, string $expectedSuggestedNewsTitle): array
    {
        $unexpectedSuggestedNewsTitles = [];

        foreach ($viewPage->filter('tbody tr') as $content) {
            $element = new Crawler($content);
            $suggestedNewsTitle = $element->filter('td')->first()->text();

            if ($suggestedNewsTitle !== $expectedSuggestedNewsTitle) {
                $unexpectedSuggestedNewsTitles[] = $suggestedNewsTitle;
            }
        }

        return $unexpectedSuggestedNewsTitles;
    }
}
