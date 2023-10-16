<?php

namespace Tests\Functional\Domain\MailingBlockAd\Command\Handler;

use App\Domain\MailingBlockAd\Command\UpdateMailingBlockAdCommand;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\MailingBlockAd\LoadMailingBlockAd;
use Tests\Functional\TestCase;

class UpdateMailingBlockAdHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadMailingBlockAd::class,
        ])->getReferenceRepository();

        $mailingBlockAd = $referenceRepository->getReference(LoadMailingBlockAd::REFERENCE_NAME);

        $command = new UpdateMailingBlockAdCommand($mailingBlockAd);

        $command->title = 'MailingBlockAd title';
        $command->data = 'MailingBlockAd data';
        $command->image = new Image('image.jpg');
        $command->startAt = Carbon::now()->subDay();
        $command->finishAt = Carbon::now();

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->title, $mailingBlockAd->getTitle());
        $this->assertEquals($command->data, $mailingBlockAd->getData());
        $this->assertEquals($command->image, $mailingBlockAd->getImage());
        $this->assertEquals($command->startAt, $mailingBlockAd->getStartAt());
        $this->assertEquals($command->finishAt, $mailingBlockAd->getFinishAt());
    }
}
