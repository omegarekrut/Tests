<?php

namespace Tests\Functional\Domain\Record\News\Command\Handler;

use App\Domain\Record\News\Command\UpdateNewsCommand;
use App\Domain\Record\News\Entity\News;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\Record\LoadNews;
use Tests\Functional\TestCase;

/**
 * @group News
 */
class UpdateNewsCommandHandlerTest extends TestCase
{
    public function testNewsIsChanged(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNews::class,
        ])->getReferenceRepository();

        $news = $referenceRepository->getReference(LoadNews::getRandReferenceName());
        assert($news instanceof News);

        $command = new UpdateNewsCommand($news);
        $command->title = 'News title';
        $command->preview = 'new preview';
        $command->priority = $news->getPriority() + 1;
        $command->text = 'News text';
        $command->actual = new \DateTime('tomorrow');
        $command->image = new Image('new-image.jpg');

        $now = Carbon::create();
        Carbon::setTestNow($now);

        try {
            $this->getCommandBus()->handle($command);

            $this->assertEquals($command->preview, $news->getPreview());
            $this->assertEquals($command->priority, $news->getPriority());
            $this->assertEquals($command->text, $news->getText());
            $this->assertNotEmpty($news->getActualDateAt());
            $this->assertEquals($command->actual->format('Y-m-d H:i:s'), $news->getActualDateAt()->format('Y-m-d H:i:s'));
            $this->assertEquals((string) $command->image, (string) $news->getImage());
            $this->assertEquals($now, $news->getUpdatedAt());
        } finally {
            Carbon::setTestNow();
        }
    }
}
