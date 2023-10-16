<?php

namespace Tests\Unit\Domain\Company\Command\Handler;

use App\Domain\Company\Command\Handler\SendWishHandler;
use App\Domain\Company\Command\SendWishCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\View\WishMailFactory;
use Swift_Message;
use Tests\Unit\Mock\MailerMock;
use Tests\Unit\Mock\ServiceMailMailerResolverMock;
use Tests\Unit\TestCase;

class SendWishHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $user = $this->generateUser();

        $mailer = new MailerMock();
        $serviceMailMailerResolver = new ServiceMailMailerResolverMock($mailer);
        $wishMailFactory = $this->createMock(WishMailFactory::class);
        $company = $this->createMock(Company::class);

        $expectedMessage = new Swift_Message();
        $wishMailFactory
            ->method('buildMail')
            ->willReturn($expectedMessage);

        $command = new SendWishCommand($user, $company);
        $command->text = 'wish text';

        $handler = new SendWishHandler($serviceMailMailerResolver, $wishMailFactory);

        $handler->handle($command);

        $this->assertSame($expectedMessage, $mailer->getLastSentMessage());
    }
}
