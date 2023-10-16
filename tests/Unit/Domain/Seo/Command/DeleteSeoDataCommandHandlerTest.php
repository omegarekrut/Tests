<?php

namespace Tests\Unit\Domain\Seo\Command;

use App\Domain\Seo\Command\DeleteSeoDataCommand;
use App\Domain\Seo\Command\Handler\DeleteSeoDataHandler;
use App\Domain\Seo\Entity\SeoData;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

/**
 * @group seo
 */
class DeleteSeoDataCommandHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $seoData = $this->createMock(SeoData::class);

        $command = new DeleteSeoDataCommand($seoData);

        $objectManager = new ObjectManagerMock();
        $handler = new DeleteSeoDataHandler($objectManager);

        $handler->handle($command);

        $this->assertTrue($objectManager->isRemoved($seoData));
    }
}
