<?php

namespace Tests\Functional\Domain\User\View\Notification\ReferenceFormatter;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\View\Notification\ConcreteNotificationViewFactory\ReferenceFormatter\RecordReferenceFormatter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class RecordReferenceFormatterTest extends TestCase
{
    /** @var RecordReferenceFormatter */
    private $recordReferenceFormatter;
    /** @var Record */
    private $record;
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
        ])->getReferenceRepository();

        $this->recordReferenceFormatter = $this->getContainer()->get(RecordReferenceFormatter::class);
        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->urlGenerator = $this->getContainer()->get('router');
    }

    protected function tearDown(): void
    {
        unset(
            $this->recordReferenceFormatter,
            $this->record,
            $this->urlGenerator
        );

        parent::tearDown();
    }

    public function testRecordReferenceMustContainsRecordTitleAndViewUrl(): void
    {
        $expectedViewUrl = $this->urlGenerator->generate('article_view', [
            'article' => $this->record->getId(),
        ]);

        $reference = $this->recordReferenceFormatter->formatReference($this->record);

        $this->assertStringContainsString($expectedViewUrl, $reference);
        $this->assertStringContainsString($this->record->getTitle(), $reference);
    }
}
