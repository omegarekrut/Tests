<?php

namespace Tests\Unit\Domain\Seo\Command;

use App\Domain\Seo\Command\CreateSeoDataCommand;
use App\Domain\Seo\Command\Handler\CreateSeoDataHandler;
use App\Domain\Seo\Entity\SeoData;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

/**
 * @group seo
 */
class CreateSeoDataCommandHandlerTest extends TestCase
{
    /** @var CreateSeoDataCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new CreateSeoDataCommand();
        $this->command->uri = '/seo_test_uri/';
        $this->command->title = 'title';
        $this->command->h1 = 'h1';
        $this->command->description = 'description';
    }

    public function testHandle(): void
    {
        $objectManager = new ObjectManagerMock();

        $commandHandler = new CreateSeoDataHandler($objectManager);
        $commandHandler->handle($this->command);

        /** @var SeoData $savedSeoData */
        $savedSeoData = $objectManager->getLastPersistedObject();

        $this->assertEquals($this->command->uri, $savedSeoData->getUri());
        $this->assertEquals($this->command->title, $savedSeoData->getTemplateTitle());
        $this->assertEquals($this->command->h1, $savedSeoData->getTemplateH1());
        $this->assertEquals($this->command->description, $savedSeoData->getTemplateDescription());
    }
}
