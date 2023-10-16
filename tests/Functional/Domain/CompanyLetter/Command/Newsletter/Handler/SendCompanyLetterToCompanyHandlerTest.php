<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\Newsletter\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Repository\CompanyRepository;
use App\Domain\CompanyLetter\Command\Newsletter\Handler\SendCompanyLetterToCompanyHandler;
use App\Domain\CompanyLetter\Command\Newsletter\SendCompanyLetterToCompanyCommand;
use App\Domain\CompanyLetter\Entity\CompanyLetter;
use App\Domain\CompanyLetter\Mail\CompanyLetterMailFactory;
use App\Domain\CompanyLetter\Repository\CompanyLetterRepository;
use App\Domain\Mailing\ServiceMailMailerResolver;
use App\Domain\User\Entity\Email;
use App\Domain\User\Entity\User;
use App\Module\Owner\AnonymousOwner;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\CompanyLetter\LoadCompanyLetterForPreviousMonth;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\MailerMock;
use Tests\Unit\Mock\ServiceMailMailerResolverMock;

class SendCompanyLetterToCompanyHandlerTest extends TestCase
{
    private CompanyLetter $companyLetterForSending;
    private CompanyLetterMailFactory $companyLetterMailFactory;
    private ServiceMailMailerResolver $serviceMailMailerResolver;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyLetterForPreviousMonth::class,
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $this->companyLetterForSending = $referenceRepository->getReference(LoadCompanyLetterForPreviousMonth::REFERENCE_NAME);
        $this->company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);

        $this->companyLetterMailFactory = $this->getContainer()->get(CompanyLetterMailFactory::class);
        $this->serviceMailMailerResolver = new ServiceMailMailerResolverMock(new MailerMock());
    }

    protected function tearDown(): void
    {
        unset(
            $this->serviceMailMailerResolver,
            $this->companyLetterMailFactory,
            $this->companyLetterForSending,
            $this->company,
        );

        parent::tearDown();
    }

    public function testCompanyLetterIsSent(): void
    {
        $expectedSentMessage = $this->companyLetterMailFactory->buildCompanyLetterMail(
            $this->companyLetterForSending,
            $this->company
        );

        $companyLetterId = $this->companyLetterForSending->getId();
        $companyId = $this->company->getId();

        $sendCompanyLetterCommand = new SendCompanyLetterToCompanyCommand($companyLetterId, $companyId);
        $this->getSendCompanyLetterToCompanyHandler()->handle($sendCompanyLetterCommand);

        $sentMessage = $this->serviceMailMailerResolver->resolveMailer()->getLastSentMessage();

        $this->assertEquals($expectedSentMessage->getSubject(), $sentMessage->getSubject());
        $this->assertEquals($expectedSentMessage->getBody(), $sentMessage->getBody());
        $this->assertEquals($expectedSentMessage->getFrom(), $sentMessage->getFrom());
        $this->assertEquals([$this->company->getOwner()->getEmailAddress() => null], $sentMessage->getTo());
    }

    public function testNotSendCompanyLetterForCompanyWithAnonymousOwner(): void
    {
        $companyLetterId = $this->companyLetterForSending->getId();

        $this->company = $this->createMock(Company::class);
        $this->company
            ->method('getId')
            ->willReturn(Uuid::uuid4());

        $this->company
            ->method('getOwner')
            ->willReturn(new AnonymousOwner());

        $sendCompanyLetterCommand = new SendCompanyLetterToCompanyCommand($companyLetterId, $this->company->getId());
        $this->getSendCompanyLetterToCompanyHandler()->handle($sendCompanyLetterCommand);

        $this->assertNull($this->serviceMailMailerResolver->resolveMailer()->getLastSentMessage());
    }

    public function testNotSendCompanyLetterForCompanyWithOwnerWithNotConfirmedEmail(): void
    {
        $companyLetterId = $this->companyLetterForSending->getId();

        $companyOwner = $this->createMock(User::class);
        $companyOwner
            ->method('canBeDisturbedByEmail')
            ->willReturn(false);

        $this->company = $this->createMock(Company::class);
        $this->company
            ->method('getId')
            ->willReturn(Uuid::uuid4());

        $this->company
            ->method('getOwner')
            ->willReturn($companyOwner);

        $sendCompanyLetterCommand = new SendCompanyLetterToCompanyCommand($companyLetterId, $this->company->getId());
        $this->getSendCompanyLetterToCompanyHandler()->handle($sendCompanyLetterCommand);

        $this->assertNull($this->serviceMailMailerResolver->resolveMailer()->getLastSentMessage());
    }

    public function testNotSendCompanyLetterForCompanyWithOwnerWithDisabledDisturbedByEmail(): void
    {
        $companyLetterId = $this->companyLetterForSending->getId();

        $companyOwner = $this->createMock(User::class);
        $companyOwner
            ->method('getEmail')
            ->willReturn(new Email('test@email.com'));

        $this->company = $this->createMock(Company::class);
        $this->company
            ->method('getId')
            ->willReturn(Uuid::uuid4());

        $this->company
            ->method('getOwner')
            ->willReturn($companyOwner);

        $sendCompanyLetterCommand = new SendCompanyLetterToCompanyCommand($companyLetterId, $this->company->getId());
        $this->getSendCompanyLetterToCompanyHandler()->handle($sendCompanyLetterCommand);

        $this->assertNull($this->serviceMailMailerResolver->resolveMailer()->getLastSentMessage());
    }

    private function getSendCompanyLetterToCompanyHandler(): SendCompanyLetterToCompanyHandler
    {
        return new SendCompanyLetterToCompanyHandler(
            $this->getCompanyRepository(),
            $this->serviceMailMailerResolver,
            $this->companyLetterMailFactory,
            $this->getCompanyLetterRepository()
        );
    }

    private function getCompanyRepository(): CompanyRepository
    {
        $companyRepositoryMock = $this->createMock(CompanyRepository::class);
        $companyRepositoryMock
            ->method('findById')
            ->willReturn($this->company);

        return $companyRepositoryMock;
    }

    private function getCompanyLetterRepository(): CompanyLetterRepository
    {
        $companyLetterRepositoryMock = $this->createMock(CompanyLetterRepository::class);
        $companyLetterRepositoryMock
            ->method('findById')
            ->willReturn($this->companyLetterForSending);

        return $companyLetterRepositoryMock;
    }
}
