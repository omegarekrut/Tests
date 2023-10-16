<?php

namespace Tests\Unit\Domain\Advertising\Mail;

use App\Domain\Advertising\Command\SendQuestionCommand;
use App\Domain\Advertising\Mail\AdvertisingMailFactory;
use Tests\Unit\Helper\TwigEnvironmentTrait;
use Tests\Unit\TestCase;

/**
 * @group advertising
 * @group public-email
 */
class AdvertisingEmailTest extends TestCase
{
    use TwigEnvironmentTrait;

    public function testBuildQuestionMail()
    {
        $advertisingMailFactory = new AdvertisingMailFactory($this->mockTwigEnvironment(
            'mail/advertising/advertising_email.html.twig',
            [
                'userName' => 'userName',
                'userEmail' => 'userEmail',
                'message' => 'message',
                'host' => 'fishingsib',
            ],
            'Twig template'
        ), 'marketolog@example.com', 'fishingsib');

        $sendQuestionCommand = new SendQuestionCommand();
        $sendQuestionCommand->userName = 'userName';
        $sendQuestionCommand->userEmail = 'userEmail';
        $sendQuestionCommand->message = 'message';

        $swiftMessage = $advertisingMailFactory->buildQuestionMail($sendQuestionCommand);

        $this->assertEquals('Вопрос со страницы рекламы', $swiftMessage->getSubject());
        $this->assertEquals(['marketolog@example.com' => null], $swiftMessage->getTo());
        $this->assertEquals('Twig template', $swiftMessage->getBody());
    }
}
