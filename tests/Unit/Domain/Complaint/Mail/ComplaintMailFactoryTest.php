<?php

namespace Tests\Unit\Domain\Complaint\Mail;

use App\Domain\Complaint\Command\SendComplaintCommand;
use App\Domain\Complaint\Mail\ComplaintMailFactory;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserByPermissionRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Unit\Helper\TwigEnvironmentTrait;
use Tests\Unit\TestCase;

class ComplaintMailFactoryTest extends TestCase
{
    use TwigEnvironmentTrait;

    public function testBuildComplaintMail(): void
    {
        $complaintMailFactory = new ComplaintMailFactory($this->mockTwigEnvironment(
            'mail/complaint/complaint_email.html.twig',
            [
                'username' => 'ivan',
                'reason' => 'reason text',
                'recordUri' => '/articles/view/1000/',
                'host' => 'fishingsib.loc',
            ],
            'Twig template'
        ), $this->getMockUserByPermissionRepository(), 'fishingsib.loc');

        $command = new SendComplaintCommand($this->getMockUser(), '/articles/view/1000/');
        $command->text = 'reason text';
        $command->image = new UploadedFile(
            sprintf('%simage20x29.jpeg', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            'image/jpeg',
            filesize(sprintf('%simage20x29.jpeg', $this->getDataFixturesFolder())),
            UPLOAD_ERR_OK,
            true
        );
        $expectedImageHeaders = sprintf('%s; name=%s', $command->image->getMimeType(), $command->image->getClientOriginalName());

        $swiftMessage = $complaintMailFactory->buildComplaintMail($command->getComplainant(), $command->text, $command->getRecordUri(), $command->image);

        $this->assertEquals('Сайт FishingSib.ru: сообщение о нарушении', $swiftMessage->getSubject());
        $this->assertEquals(['admin@gmail.com' => null], $swiftMessage->getTo());
        $this->assertEquals(['admin@gmail.com' => null], $swiftMessage->getFrom());
        $this->assertEquals('Twig template', $swiftMessage->getBody());
        $this->assertEquals($expectedImageHeaders, $swiftMessage->getChildren()[0]->getHeaders()->get('Content-Type')->getFieldBody());
    }

    private function getMockUserByPermissionRepository(): UserByPermissionRepository
    {
        $mock = $this->createMock(UserByPermissionRepository::class);

        $mock
            ->method('findWithPermissionForSendComplaint')
            ->willReturn([$this->getMockUser()]);

        return $mock;
    }

    private function getMockUser(): User
    {
        $mock = $this->createMock(User::class);

        $mock
            ->method('getEmailAddress')
            ->willReturn('admin@gmail.com');

        $mock
            ->method('getUsername')
            ->willReturn('ivan');

        return $mock;
    }
}
