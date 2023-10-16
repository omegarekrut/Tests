<?php

namespace Tests\Unit\Domain\Complaint\Mail;

use App\Domain\Complaint\Command\SendReportInaccuracyCommand;
use App\Domain\Complaint\Mail\ReportInaccuracyMailFactory;
use App\Domain\User\Entity\User;
use Tests\Unit\Helper\TwigEnvironmentTrait;
use Tests\Unit\TestCase;

class ReportInaccuracyMailFactoryTest extends TestCase
{
    use TwigEnvironmentTrait;

    private const RECIPIENT_EMAILS = ['test_help_site@mail.ru', 'test_moderator@yandex.ru'];

    private ReportInaccuracyMailFactory $reportInaccuracyMailFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reportInaccuracyMailFactory = new ReportInaccuracyMailFactory(
            $this->mockTwigEnvironment(
                'mail/complaint/report_inaccuracy.html.twig',
                [
                    'username' => $this->getComplainantUserMock()->getUsername(),
                    'reason' => 'reason text',
                    'pageUri' => '/companies/zao-radio/8NhehFRYg629B3CspJ6THD/',
                    'host' => 'fishingsib.loc',
                ],
                'Twig template'
            ),
            'fishingsib.loc',
            self::RECIPIENT_EMAILS,
        );
    }

    protected function tearDown(): void
    {
        unset($this->reportInaccuracyMailFactory);

        parent::tearDown();
    }

    public function testBuildMail(): void
    {
        $complainant = $this->getComplainantUserMock();

        $command = new SendReportInaccuracyCommand(
            $this->getComplainantUserMock(),
            '/companies/zao-radio/8NhehFRYg629B3CspJ6THD/'
        );
        $command->setText('reason text');

        $swiftMessage = $this->reportInaccuracyMailFactory->buildMail(
            $command->getComplainant(),
            $command->getText(),
            $command->getPageUri()
        );

        $this->assertEquals('Сайт FishingSib.ru: сообщение о неточности', $swiftMessage->getSubject());
        $this->assertEquals([$complainant->getEmailAddress() => null], $swiftMessage->getFrom());
        $this->assertEquals(array_fill_keys(self::RECIPIENT_EMAILS, null), $swiftMessage->getTo());
        $this->assertEquals('Twig template', $swiftMessage->getBody());
    }

    private function getComplainantUserMock(): User
    {
        return $this->getUserMock('ivan', 'complainant@fishingsib.loc');
    }

    private function getUserMock(string $login, string $email): User
    {
        $mock = $this->createMock(User::class);

        $mock
            ->method('getEmailAddress')
            ->willReturn($email);

        $mock
            ->method('getUsername')
            ->willReturn($login);

        return $mock;
    }
}
