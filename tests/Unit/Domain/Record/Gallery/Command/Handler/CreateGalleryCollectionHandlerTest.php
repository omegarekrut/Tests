<?php

namespace Tests\Unit\Domain\Record\Gallery\Command\Handler;

use App\Domain\Category\Entity\Category;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Gallery\Command\CreateGalleryCollectionCommand;
use App\Domain\Record\Gallery\Command\CreateGalleryCommand;
use App\Domain\Record\Gallery\Command\Handler\CreateGalleryCollectionHandler;
use App\Domain\User\Entity\User;
use Tests\Unit\TestCase;
use Tests\Unit\Mock\CommandBusMock;

/**
 * @group gallery
 */
class CreateGalleryCollectionHandlerTest extends TestCase
{
    private CommandBusMock $commandBus;
    private CreateGalleryCollectionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = new CommandBusMock();
        $this->handler = new CreateGalleryCollectionHandler($this->commandBus);
    }

    protected function tearDown(): void
    {
        unset(
            $this->commandBus,
            $this->handler,
        );

        parent::tearDown();
    }

    public function testGalleryCollectionIsSaved(): void
    {
        $photoLimit = 5;
        $command = new CreateGalleryCollectionCommand($this->createMock(User::class), $photoLimit);

        $firstGalleryData = $this->getGalleryData('_1');
        $secondGalleryData = $this->getGalleryData('_2');

        $command->galleries = [
            $firstGalleryData,
            $secondGalleryData,
        ];

        $this->handler->handle($command);

        /**
         * @var CreateGalleryCommand[] $createGalleryCommands
         */
        $createGalleryCommands = $this->commandBus->getAllHandledCommandsOfClass(CreateGalleryCommand::class);

        $this->assertCount(2, $createGalleryCommands);
        $this->assertEquals($firstGalleryData['title'], $createGalleryCommands[0]->title);
        $this->assertEquals($secondGalleryData['title'], $createGalleryCommands[1]->title);
    }

    public function testGalleryCollectionIsCreatedByCompanyAuthor(): void
    {
        $photoLimit = 5;
        $command = new CreateGalleryCollectionCommand($this->createMock(User::class), $photoLimit);
        $command->companyAuthor = $this->createMock(Company::class);

        $galleryData = $this->getGalleryData('_1');

        $command->galleries = [$galleryData];

        $this->handler->handle($command);

        /**
         * @var CreateGalleryCommand[] $createGalleryCommands
         */
        $createGalleryCommands = $this->commandBus->getAllHandledCommandsOfClass(CreateGalleryCommand::class);

        $this->assertCount(1, $createGalleryCommands);
        $this->assertEquals($galleryData['title'], $createGalleryCommands[0]->title);
        $this->assertEquals($command->companyAuthor, $createGalleryCommands[0]->companyAuthor);
    }

    public function testGalleryCollectionIsLimited(): void
    {
        $photoLimit = 1;
        $command = new CreateGalleryCollectionCommand($this->createMock(User::class), $photoLimit);

        $firstGalleryData = $this->getGalleryData('_1');
        $secondGalleryData = $this->getGalleryData('_2');

        $command->galleries = [
            $firstGalleryData,
            $secondGalleryData,
        ];

        $this->handler->handle($command);

        /**
         * @var CreateGalleryCommand[] $createGalleryCommands
         */
        $createGalleryCommands = $this->commandBus->getAllHandledCommandsOfClass(CreateGalleryCommand::class);

        $this->assertCount(1, $createGalleryCommands);
        $this->assertEquals($firstGalleryData['title'], $createGalleryCommands[0]->title);
    }

    /**
     * @return mixed[]
     */
    private function getGalleryData(string $uniqKey): array
    {
        return [
            'category' => $this->createMock(Category::class),
            'title' => 'title'.$uniqKey,
            'data' => 'data'.$uniqKey,
            'imageName' => 'imageName.jpeg',
            'imageRotationAngle' => 0,
            'regionId' => 'otherRegion',
        ];
    }
}
