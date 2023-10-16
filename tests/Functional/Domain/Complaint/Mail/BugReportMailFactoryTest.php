<?php

namespace Tests\Functional\Domain\Complaint\Mail;

use App\Domain\Complaint\Mail\BugReportMailFactory;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class BugReportMailFactoryTest extends TestCase
{
    private User $complainant;
    private BugReportMailFactory $bugReportMailFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([LoadTestUser::class])->getReferenceRepository();

        $this->complainant = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->bugReportMailFactory = $this->getContainer()->get(BugReportMailFactory::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->complainant,
            $this->bugReportMailFactory,
        );

        parent::tearDown();
    }

    public function testBuildMail(): void
    {
        $expectedBugReport = 'bug report';
        $expectedBugLocationUrl = 'page/url';
        $image = new UploadedFile(
            sprintf('%simage20x29.jpeg', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            'image/jpeg',
            filesize(sprintf('%simage20x29.jpeg', $this->getDataFixturesFolder())),
            UPLOAD_ERR_OK,
            true
        );
        $expectedImageHeaders = sprintf('%s; name=%s', $image->getMimeType(), $image->getClientOriginalName());

        $swiftMessage = $this->bugReportMailFactory->buildMail($this->complainant, $expectedBugReport, $expectedBugLocationUrl, $image);

        $this->assertEquals('Сайт FishingSib.ru: сообщение об ошибке', $swiftMessage->getSubject());
        $this->assertEquals([(string) $this->complainant->getEmail() => null], $swiftMessage->getFrom());
        $this->assertStringContainsString($expectedBugReport, $swiftMessage->getBody());
        $this->assertStringContainsString($expectedBugLocationUrl, $swiftMessage->getBody());
        $this->assertEquals($expectedImageHeaders, $swiftMessage->getChildren()[0]->getHeaders()->get('Content-Type')->getFieldBody());
    }
}
