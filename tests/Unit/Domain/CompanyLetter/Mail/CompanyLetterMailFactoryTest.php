<?php

namespace Tests\Unit\Domain\CompanyLetter\Mail;

use App\Domain\Company\Entity\Company;
use App\Domain\CompanyLetter\Entity\CompanyLetter;
use App\Domain\CompanyLetter\Mail\CompanyLetterMailFactory;
use Carbon\Carbon;
use Swift_Message;
use Tests\Unit\Helper\TwigEnvironmentTrait;
use Tests\Unit\TestCase;

class CompanyLetterMailFactoryTest extends TestCase
{
    use TwigEnvironmentTrait;

    public function testBuildCompanyLetterMail(): void
    {
        $companyLetter = $this->getCompanyLetter(10);
        $company = $this->createMock(Company::class);

        $factory = $this->createCompanyLetterMailFactory($companyLetter, $company);

        $companyLetterMail = $factory->buildCompanyLetterMail($companyLetter, $company);

        $this->assertInstanceOf(Swift_Message::class, $companyLetterMail);
        $this->assertEquals(['subscribe@fishingsib.ru' => 'FishingSib'], $companyLetterMail->getFrom());
        $this->assertEquals(
            'Дайджест событий вашего бизнес-аккаунта на сайте fishingsib.ru за октябрь 2022',
            $companyLetterMail->getSubject()
        );
        $this->assertEquals('company_letter_mail_body', $companyLetterMail->getBody());
    }

    private function getCompanyLetter(int $companyLetterNumber): CompanyLetter
    {
        $companyLetter = $this->createMock(CompanyLetter::class);

        $companyLetter->method('getNumber')
            ->willReturn($companyLetterNumber);
        $companyLetter->method('getPeriodDate')
            ->willReturn((new Carbon('2022-11-01'))->subMonth()->startOfMonth());

        return $companyLetter;
    }

    private function createCompanyLetterMailFactory(CompanyLetter $companyLetter, Company $company): CompanyLetterMailFactory
    {
        return new CompanyLetterMailFactory(
            'subscribe@fishingsib.ru',
            'FishingSib',
            $this->mockTwigEnvironment('mail/company/mailing/company_mailing.html.inky.twig', [
                'companyLetterNumber' => $companyLetter->getNumber(),
                'company' => $company,
                'periodDate' => $companyLetter->getPeriodDate(),
            ], 'company_letter_mail_body'),
        );
    }
}
