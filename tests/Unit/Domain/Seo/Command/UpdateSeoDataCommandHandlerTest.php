<?php

namespace Tests\Unit\Domain\Seo\Command;

use App\Domain\Seo\Command\Handler\UpdateSeoDataHandler;
use App\Domain\Seo\Command\UpdateSeoDataCommand;
use App\Domain\Seo\Entity\SeoData;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

/**
 * @group seo
 */
class UpdateSeoDataCommandHandlerTest extends TestCase
{
    /** @var SeoData */
    private $seoData;
    /** @var UpdateSeoDataCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoData = new SeoData(
            '/old_uri/',
            'old title',
            'old h1',
            'old description'
        );

        $this->command = new UpdateSeoDataCommand($this->seoData);
        $this->command->uri = '/new_uri/';
        $this->command->title = 'new title';
        $this->command->h1 = 'new h1';
        $this->command->description = 'new description';
    }

    public function testHandle(): void
    {
        $objectManager = new ObjectManagerMock();

        $handler = new UpdateSeoDataHandler($objectManager);
        $handler->handle($this->command);

        $this->assertEquals($this->command->uri, $this->seoData->getUri());
        $this->assertEquals($this->command->title, $this->seoData->getTemplateTitle());
        $this->assertEquals($this->command->h1, $this->seoData->getTemplateH1());
        $this->assertEquals($this->command->description, $this->seoData->getTemplateDescription());
    }
}
