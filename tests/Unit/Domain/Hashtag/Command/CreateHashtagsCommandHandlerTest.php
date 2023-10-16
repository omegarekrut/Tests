<?php

namespace Tests\Unit\Domain\Hashtag\Command;

use App\Domain\Hashtag\Command\CreateHashtagsCommand;
use App\Domain\Hashtag\Command\Handler\CreateHashtagsHandler;
use App\Domain\Hashtag\Entity\Hashtag;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

class CreateHashtagsCommandHandlerTest extends TestCase
{
    private const HASHTAG_NAME = 'зимняяРыбалка';

    public function testHandle(): void
    {
        $objectManager = new ObjectManagerMock();

        $command = new CreateHashtagsCommand([self::HASHTAG_NAME]);
        $handler = new CreateHashtagsHandler($objectManager);
        $handler->handle($command);

        /** @var Hashtag $hashtag */
        $hashtag = $objectManager->getLastPersistedObject();

        $this->assertEquals(self::HASHTAG_NAME, $hashtag->getName());
    }
}
