<?php

namespace Tests\Functional\Domain\User\View\Notification\ReferenceFormatter;

use App\Domain\SuggestedNews\Entity\SuggestedNews;
use App\Domain\User\View\Notification\ConcreteNotificationViewFactory\ReferenceFormatter\SuggestedNewsReferenceFormatter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\SuggestedNews\LoadSuggestedNewsByUserFixture;
use Tests\Functional\TestCase;

class SuggestedNewsReferenceFormatterTest extends TestCase
{
    private SuggestedNewsReferenceFormatter $suggestedNewsReferenceFormatter;
    private UrlGeneratorInterface $urlGenerator;
    private SuggestedNews $suggestedNews;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadSuggestedNewsByUserFixture::class,
        ])->getReferenceRepository();

        $this->suggestedNewsReferenceFormatter = $this->getContainer()->get(SuggestedNewsReferenceFormatter::class);
        $this->suggestedNews = $referenceRepository->getReference(LoadSuggestedNewsByUserFixture::REFERENCE_NAME);
        $this->urlGenerator = $this->getContainer()->get('router');
    }

    protected function tearDown(): void
    {
        unset(
            $this->suggestedNewsReferenceFormatter,
            $this->suggestedNews,
            $this->urlGenerator
        );

        parent::tearDown();
    }

    public function testSuggestedNewsReferenceMustContainsSuggestedNewsTitleAndViewUrl(): void
    {
        $expectedViewUrl = $this->urlGenerator->generate('admin_suggested_news_view', [
            'suggestedNews' => $this->suggestedNews->getId(),
        ]);

        $reference = $this->suggestedNewsReferenceFormatter->formatReference($this->suggestedNews);

        $this->assertStringContainsString($expectedViewUrl, $reference);
        $this->assertStringContainsString($this->suggestedNews->getTitle(), $reference);
    }
}
