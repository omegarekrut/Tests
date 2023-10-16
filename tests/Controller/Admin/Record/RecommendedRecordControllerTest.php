<?php

namespace Tests\Controller\Admin\Record;

use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecords;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleByCompanyAuthor;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoByCompanyAuthor;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

class RecommendedRecordControllerTest extends TestCase
{
    private const RECOMMENDED_RECORDS_INDEX_PATH = '/admin/recommended-record/';
    private const RECORDS_LIST_PATH = '/admin/recommended-record/list-records/';

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();
    }

    public function testIndex(): void
    {
        $browser = $this->getBrowser()->loginUser($this->loadAdmin());
        $viewPage = $browser->request('GET', self::RECOMMENDED_RECORDS_INDEX_PATH);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString('Список рекомендуемого', $viewPage->html());
    }

    public function testFilterRecommendedRecordsList(): void
    {
        $admin = $this->loadAdmin();

        $referenceRepository = $this->loadFixtures([
            LoadRecommendedRecords::class,
        ], true)->getReferenceRepository();

        $recommendedArticle = $referenceRepository->getReference(LoadRecommendedRecords::REFERENCE_ARTICLE_NAME);
        assert($recommendedArticle instanceof RecommendedRecord);

        $recommendedVideo = $referenceRepository->getReference(LoadRecommendedRecords::REFERENCE_VIDEO_NAME);
        assert($recommendedVideo instanceof RecommendedRecord);

        $browser = $this->getBrowser()->loginUser($admin);
        $browser->request('GET', self::RECOMMENDED_RECORDS_INDEX_PATH);

        $expectedRecommendedRecordTitle = $recommendedArticle->getRecord()->getTitle();
        $unexpectedRecommendedRecordTitle = $recommendedVideo->getRecord()->getTitle();

        $viewPage = $browser->submitForm('Поиск', [
            'recommended_record_search_in_admin[recordTitle]' => $expectedRecommendedRecordTitle,
        ], 'GET');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString($expectedRecommendedRecordTitle, $viewPage->filter('tbody')->html());
        $this->assertStringNotContainsString($unexpectedRecommendedRecordTitle, $viewPage->filter('tbody')->html());

        $resetLink = $viewPage->selectLink('Сбросить')->link();
        $viewPage = $browser->click($resetLink);

        $expectedRecommendedRecordTitles = [
            $recommendedArticle->getRecord()->getTitle(),
            $recommendedVideo->getRecord()->getTitle(),
        ];

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        foreach ($expectedRecommendedRecordTitles as $expectedRecommendedRecordTitle) {
            $this->assertStringContainsString($expectedRecommendedRecordTitle, $viewPage->filter('tbody')->html());
        }
    }

    public function testRecordsList(): void
    {
        $browser = $this->getBrowser()->loginUser($this->loadAdmin());
        $viewPage = $browser->request('GET', self::RECOMMENDED_RECORDS_INDEX_PATH);

        $addToRecommendedLink = $viewPage->selectLink('Добавить рекомендуемое')->link();
        $viewPage = $browser->click($addToRecommendedLink);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString('Добавление рекомендуемого', $viewPage->html());
    }

    public function testFilterRecordsList(): void
    {
        $admin = $this->loadAdmin();

        $referenceRepository = $this->loadFixtures([
            LoadArticleByCompanyAuthor::class,
            LoadVideoByCompanyAuthor::class,
        ], true)->getReferenceRepository();

        $article = $referenceRepository->getReference(LoadArticleByCompanyAuthor::REFERENCE_NAME);
        assert($article instanceof Record);

        $video = $referenceRepository->getReference(LoadVideoByCompanyAuthor::REFERENCE_NAME);
        assert($video instanceof Record);

        $browser = $this->getBrowser()->loginUser($admin);
        $browser->request('GET', self::RECORDS_LIST_PATH);

        $expectedRecordTitle = $article->getTitle();
        $unexpectedRecordTitle = $video->getTitle();

        $viewPage = $browser->submitForm('Поиск', [
            'record_search_in_admin_for_recommended_record[title]' => $expectedRecordTitle,
        ], 'GET');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString($expectedRecordTitle, $viewPage->filter('tbody')->html());
        $this->assertStringNotContainsString($unexpectedRecordTitle, $viewPage->filter('tbody')->html());

        $resetLink = $viewPage->selectLink('Сбросить')->link();
        $viewPage = $browser->click($resetLink);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString($expectedRecordTitle, $viewPage->filter('tbody')->html());
        $this->assertStringContainsString($unexpectedRecordTitle, $viewPage->filter('tbody')->html());
    }

    public function testCreateRecommendedRecord(): void
    {
        $admin = $this->loadAdmin();

        $this->loadFixtures([
            LoadArticleByCompanyAuthor::class,
        ], true)->getReferenceRepository();

        $browser = $this->getBrowser()->loginUser($admin);
        $viewPage = $browser->request('GET', self::RECORDS_LIST_PATH);

        $addToRecommendedLink = $viewPage->filter('[title="Добавить в рекомендуемое"]')->link();
        $browser->click($addToRecommendedLink);

        $viewPage = $browser->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertSeeAlertInPageContent('success', 'Рекомендуемая запись успешно добавлена.', $viewPage->html());
    }

    public function testShowRecommendedRecord(): void
    {
        $admin = $this->loadAdmin();

        $this->loadFixtures([
            LoadRecommendedRecords::class,
        ], true)->getReferenceRepository();

        $browser = $this->getBrowser()->loginUser($admin);
        $viewPage = $browser->request('GET', self::RECOMMENDED_RECORDS_INDEX_PATH);

        $showLink = $viewPage->filter('[title="Показать в рекомендуемом"]')->link();
        $browser->click($showLink);

        $viewPage = $browser->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertSeeAlertInPageContent('success', 'Рекомендуемая запись отображена.', $viewPage->html());
    }

    public function testHideRecommendedRecord(): void
    {
        $admin = $this->loadAdmin();

        $this->loadFixtures([
            LoadRecommendedRecords::class,
        ], true)->getReferenceRepository();

        $browser = $this->getBrowser()->loginUser($admin);
        $viewPage = $browser->request('GET', self::RECOMMENDED_RECORDS_INDEX_PATH);

        $hideLink = $viewPage->filter('[title="Скрыть из рекомендуемого"]')->link();
        $browser->click($hideLink);

        $viewPage = $browser->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertSeeAlertInPageContent('success', 'Рекомендуемая запись скрыта.', $viewPage->html());
    }

    public function testDeleteRecommendedRecord(): void
    {
        $admin = $this->loadAdmin();

        $referenceRepository = $this->loadFixtures([
            LoadRecommendedRecords::class,
        ], true)->getReferenceRepository();

        $recommendedArticle = $referenceRepository->getReference(LoadRecommendedRecords::REFERENCE_ARTICLE_NAME);
        assert($recommendedArticle instanceof RecommendedRecord);

        $recommendedRecordTitleToDelete = $recommendedArticle->getRecord()->getTitle();

        $browser = $this->getBrowser()->loginUser($admin);
        $browser->request('GET', self::RECOMMENDED_RECORDS_INDEX_PATH);

        $deletePath = sprintf('/admin/recommended-record/%s/remove/', $recommendedArticle->getId());
        $browser->request('GET', $deletePath);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertEquals('{"status":"ok"}', $this->getBrowser()->getResponse()->getContent());

        $viewPage = $browser->request('GET', self::RECOMMENDED_RECORDS_INDEX_PATH);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringNotContainsString($recommendedRecordTitleToDelete, $viewPage->filter('tbody')->html());
    }

    public function testEditRecommendedRecordPriority(): void
    {
        $admin = $this->loadAdmin();

        $this->loadFixtures([
            LoadRecommendedRecords::class,
        ], true)->getReferenceRepository();

        $browser = $this->getBrowser()->loginUser($admin);
        $viewPage = $browser->request('GET', self::RECOMMENDED_RECORDS_INDEX_PATH);

        $editPriorityLink = $viewPage->filter('[title="Изменить"]')->link();
        $browser->click($editPriorityLink);

        $browser->submitForm('Сохранить', [
            'update_recommended_record[priority]' => 55,
        ]);

        $this->assertTrue($browser->getResponse()->isRedirect(self::RECOMMENDED_RECORDS_INDEX_PATH));

        $viewPage = $browser->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertSeeAlertInPageContent('success', 'Рекомендованная новость успешно обновлена.', $viewPage->html());
    }

    private function loadAdmin(): User
    {
        return $this->loadFixture(LoadAdminUser::class, User::class);
    }
}
