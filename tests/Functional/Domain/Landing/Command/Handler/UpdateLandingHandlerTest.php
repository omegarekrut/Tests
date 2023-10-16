<?php

namespace Tests\Functional\Domain\Landing\Command\Handler;

use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Landing\Command\UpdateLandingCommand;
use App\Domain\Landing\Entity\Landing;
use Tests\DataFixtures\ORM\Landing\LoadTestLandings;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\Functional\TestCase;

/**
 * @group landing
 */
class UpdateLandingHandlerTest extends TestCase
{
    public function testLandingsMustBeEdited(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestLandings::class,
            LoadHashtags::class
        ])->getReferenceRepository();

        /** @var Landing $landing */
        $landing = $referenceRepository->getReference(LoadTestLandings::REFERENCE_NAME);
        /** @var Hashtag $hashtag */
        $hashtag = $referenceRepository->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_FISHING));

        $updateLandingCommand = new UpdateLandingCommand($landing);
        $updateLandingCommand->hashtag = $hashtag;
        $updateLandingCommand->heading = 'heading';
        $updateLandingCommand->slug = 'slug';
        $updateLandingCommand->pageTopContent = 'pageTopContent';
        $updateLandingCommand->pageBottomContent = 'pageBottomContent';
        $updateLandingCommand->metaTitle = 'metaTitle';
        $updateLandingCommand->metaDescription = 'metaDescription';

        $this->getCommandBus()->handle($updateLandingCommand);

        $this->assertTrue($updateLandingCommand->hashtag === $landing->getHashtag());
        $this->assertEquals($updateLandingCommand->heading, $landing->getHeading());
        $this->assertEquals($updateLandingCommand->slug, $landing->getSlug());
        $this->assertEquals($updateLandingCommand->pageTopContent, $landing->getPageContent()->getTop());
        $this->assertEquals($updateLandingCommand->pageBottomContent, $landing->getPageContent()->getBottom());
        $this->assertEquals($updateLandingCommand->metaTitle, $landing->getMetaData()->getTitle());
        $this->assertEquals($updateLandingCommand->metaDescription, $landing->getMetaData()->getDescription());
    }
}
