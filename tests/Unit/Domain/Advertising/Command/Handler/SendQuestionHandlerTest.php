<?php

namespace Tests\Unit\Domain\Advertising\Command\Handler;

use App\Domain\Advertising\Command\Handler\SendQuestionHandler;
use App\Domain\Advertising\Command\SendQuestionCommand;
use App\Domain\Advertising\Mail\AdvertisingMailFactory;
use Swift_Message;
use Tests\Unit\Mock\MailerMock;
use Tests\Unit\Mock\ServiceMailMailerResolverMock;
use Tests\Unit\TestCase;

/**
 * @group advertising
 */
class SendQuestionHandlerTest extends TestCase
{
    public function testBuildQuestionMail(): void
    {
        $command = new SendQuestionCommand();
        $swiftMessage = new Swift_Message();
        $mailer = new MailerMock();
        $serviceMailMailerResolver = new ServiceMailMailerResolverMock($mailer);

        $handler = new SendQuestionHandler($serviceMailMailerResolver, $this->getMockAdvertisingMailFactoryMailFactory($command, $swiftMessage));
        $handler->handle($command);

        $this->assertEquals($swiftMessage, $mailer->getLastSentMessage());
    }

    private function getMockAdvertisingMailFactoryMailFactory(SendQuestionCommand $command, Swift_Message $message): AdvertisingMailFactory
    {
        $mock = $this->createMock(AdvertisingMailFactory::class);

        $mock
            ->method('buildQuestionMail')
            ->with($command)
            ->willReturn($message);

        return $mock;
    }
}
