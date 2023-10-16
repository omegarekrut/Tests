<?php

namespace Tests\Functional\Domain\MailingBlockAd\Command\Handler;

use App\Domain\MailingBlockAd\Command\CreateMailingBlockAdCommand;
use App\Domain\MailingBlockAd\Entity\MailingBlockAd;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\Functional\TestCase;
use Ramsey\Uuid\Uuid;

class CreateMailingBlockAdHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $command = new CreateMailingBlockAdCommand(Uuid::uuid4());
        $command->title = 'MailingBlockAd title';
        $command->data = 'MailingBlockAd data';
        $command->image = new Image('image.jpg');
        $command->startAt = Carbon::now()->subDay();
        $command->finishAt = Carbon::now();

        $this->getCommandBus()->handle($command);

        $mailingBlockAdRepository = $this->getEntityManager()->getRepository(MailingBlockAd::class);

        $mailingBlockAd = $mailingBlockAdRepository->find($command->id);
        assert($mailingBlockAd instanceof MailingBlockAd);

        $this->assertEquals($command->id, $mailingBlockAd->getId());
        $this->assertEquals($command->title, $mailingBlockAd->getTitle());
        $this->assertEquals($command->data, $mailingBlockAd->getData());
        $this->assertEquals($command->image, $mailingBlockAd->getImage());
        $this->assertEquals($command->startAt, $mailingBlockAd->getStartAt());
        $this->assertEquals($command->finishAt, $mailingBlockAd->getFinishAt());
    }
}
