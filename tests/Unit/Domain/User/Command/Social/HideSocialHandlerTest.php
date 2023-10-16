<?php

namespace Tests\Unit\Domain\User\Command\Social;

use App\Domain\User\Command\Social\Handler\HideSocialHandler;
use App\Domain\User\Command\Social\HideSocialCommand;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

class HideSocialHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $objectManager = new ObjectManagerMock();

        $enabledShowSocialUser = $this->generateUser()->showSocial();
        $firstCommand = new HideSocialCommand($enabledShowSocialUser);

        $disabledShowSocialUser = $this->generateUser()->hideSocial();
        $secondCommand = new HideSocialCommand($disabledShowSocialUser);

        $handler = new HideSocialHandler($objectManager);
        $handler->handle($firstCommand);
        $handler->handle($secondCommand);

        $this->assertFalse($enabledShowSocialUser->isShowedSocial());
        $this->assertFalse($disabledShowSocialUser->isShowedSocial());
    }
}
