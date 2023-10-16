<?php

namespace Tests\Functional\Domain\Landing\Command\Handler;

use App\Domain\Landing\Command\CreateLandingCommand;
use App\Domain\Landing\Entity\Landing;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\Functional\TestCase;

/**
 * @group landing
 */
class CreateLandingHandlerTest extends TestCase
{
    public function testLandingsMustBeCreated(): void
    {
        $referenceRepository = $this->loadFixtures([LoadHashtags::class])->getReferenceRepository();
        $hashtag = $referenceRepository->getReference(
            LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_FISHING)
        );

        $createLandingCommand = new CreateLandingCommand();
        $createLandingCommand->hashtag = $hashtag;
        $createLandingCommand->slug = 'slug';
        $createLandingCommand->heading = 'heading';
        $createLandingCommand->pageTopContent = 'pageTopContent';
        $createLandingCommand->pageBottomContent = 'pageBottomContent';
        $createLandingCommand->metaTitle = 'metaTitle';
        $createLandingCommand->metaDescription = 'metaDescription';

        $this->getCommandBus()->handle($createLandingCommand);

        $landingRepository = $this->getEntityManager()->getRepository(Landing::class);
        $createdLanding = $landingRepository->findBySlug($createLandingCommand->slug);

        $this->assertNotEmpty($createdLanding);
        $this->assertEquals($createLandingCommand->hashtag->getSlug(), $createdLanding->getHashtag()->getSlug());
        $this->assertEquals($createLandingCommand->heading, $createdLanding->getHeading());
        $this->assertTrue($createLandingCommand->slug === $createdLanding->getSlug());
        $this->assertEquals($createLandingCommand->pageTopContent, $createdLanding->getPageContent()->getTop());
        $this->assertEquals($createLandingCommand->pageBottomContent, $createdLanding->getPageContent()->getBottom());
        $this->assertEquals($createLandingCommand->metaTitle, $createdLanding->getMetaData()->getTitle());
        $this->assertEquals($createLandingCommand->metaDescription, $createdLanding->getMetaData()->getDescription());
    }
}
