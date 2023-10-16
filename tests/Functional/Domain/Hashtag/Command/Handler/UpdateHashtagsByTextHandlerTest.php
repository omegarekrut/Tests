<?php

namespace Tests\Functional\Domain\Hashtag\Command\Handler;

use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Command\UpdateHashtagsByRecordTextCommand;
use App\Domain\Record\Map\Entity\Map;
use App\Domain\Record\Common\Entity\Record;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\DataFixtures\ORM\Record\LoadMaps;
use Tests\DataFixtures\ORM\Record\Tidings\LoadNumberedTidings;
use Tests\Functional\TestCase;

class UpdateHashtagsByTextHandlerTest extends TestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;
    /** @var Record */
    private $record;
    /** @var Hashtag */
    private $firstHashtag;
    /** @var Hashtag */
    private $secondHashtag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadNumberedTidings::class,
            LoadHashtags::class,
            LoadMaps::class,
        ])->getReferenceRepository();

        $this->record = $this->record ?? $this->referenceRepository->getReference(LoadNumberedTidings::getRandReferenceName());
        $this->firstHashtag = $this->referenceRepository->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_FISHING));
        $this->secondHashtag = $this->referenceRepository->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_WINTER_FISHING));
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->record,
            $this->firstHashtag,
            $this->secondHashtag
        );

        parent::tearDown();
    }

    public function testHashtagShouldAttachToRecordByText(): void
    {
        $this->record->rewriteTextFromDTO((object) [
            'data' => $this->getTextWithHashtag($this->firstHashtag->getName()),
        ]);

        $this->assertFalse($this->record->isAttachedHashtag($this->firstHashtag));

        $command = new UpdateHashtagsByRecordTextCommand($this->record);
        $this->getCommandBus()->handle($command);

        $this->assertTrue($this->record->isAttachedHashtag($this->firstHashtag));
    }

    /**
     * @depends testHashtagShouldAttachToRecordByText
     */
    public function testHashtagShouldDetachFromRecordByText(): void
    {
        $this->record->rewriteTextFromDTO((object) [
            'data' => $this->getTextWithHashtag($this->secondHashtag->getName()),
        ]);

        $command = new UpdateHashtagsByRecordTextCommand($this->record);
        $this->getCommandBus()->handle($command);

        $this->assertFalse($this->record->isAttachedHashtag($this->firstHashtag));
        $this->assertTrue($this->record->isAttachedHashtag($this->secondHashtag));
    }

    public function testDoNothingIfRecordNotImplementsNeededInterface(): void
    {
        /** @var Map $wrongClassRecord */
        $wrongClassRecord = $this->referenceRepository->getReference(LoadMaps::getRandReferenceName());

        $wrongClassRecord->rewriteTextFromDTO((object) [
            'data' => $this->getTextWithHashtag($this->firstHashtag->getName()),
        ]);

        $command = new UpdateHashtagsByRecordTextCommand($wrongClassRecord);
        $this->getCommandBus()->handle($command);

        $this->assertFalse($this->firstHashtag->isAttachedRecord($wrongClassRecord));
    }

    private function getTextWithHashtag(string $hashtag): string
    {
        $text = $this->getFaker()->text();

        return substr_replace($text, ' #'.$hashtag.' ', random_int(0, strlen($text)), 0);
    }
}
