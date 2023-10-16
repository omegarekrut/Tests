<?php

namespace Tests\Unit\Domain\User\Command\Social;

use App\Domain\User\Command\Social\Handler\ShowSocialHandler;
use App\Domain\User\Command\Social\ShowSocialCommand;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

class ShowSocialHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $objectManager = new ObjectManagerMock();

        $enabledShowSocialUser = $this->generateUser()->showSocial();
        $firstCommand = new ShowSocialCommand($enabledShowSocialUser);

        $disabledShowSocialUser = $this->generateUser()->hideSocial();
        $secondCommand = new ShowSocialCommand($disabledShowSocialUser);

        $handler = new ShowSocialHandler($objectManager);
        $handler->handle($firstCommand);
        $handler->handle($secondCommand);

        $this->assertTrue($enabledShowSocialUser->isShowedSocial());
        $this->assertTrue($disabledShowSocialUser->isShowedSocial());
    }
}
