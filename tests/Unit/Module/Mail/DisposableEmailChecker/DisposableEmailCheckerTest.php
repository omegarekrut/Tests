<?php

namespace Tests\Unit\Module\Mail\DisposableEmailChecker;

use App\Module\Mail\DisposableEmailChecker\DisposableEmailChecker;
use App\Module\MailCheck\MailCheckClientInterface;
use App\Module\VerifyMail\VerifyMailClientInterface;
use App\Module\WhoIs\WhoIsServiceInterface;
use App\Module\WhoIs\WhoIsServiceMock;
use Generator;
use InvalidArgumentException;
use Tests\Unit\TestCase;

class DisposableEmailCheckerTest extends TestCase
{
    /**
     * @dataProvider validEmailProvider
     */
    public function testGetCheckerResult(
        string $email,
        string $domain,
        bool $mailCheckReturnValue,
        bool $verifyMailReturnValue,
        bool $whoIsServiceReturnValue,
        bool $expectedCheckerValue
    ): void {

        $mailCheckClient = $this->createMailCheckClientMock($domain, $mailCheckReturnValue);
        $verifyMailClient = $this->createVerifyMailClientMock($email, $verifyMailReturnValue);
        $whoIsService = $this->createWhoIsServiceMock($domain, $whoIsServiceReturnValue);

        $checker = new DisposableEmailChecker($mailCheckClient, $verifyMailClient, $whoIsService);
        $value = $checker->isDisposableEmail($email);

        $this->assertEquals($expectedCheckerValue, $value);
    }

    private function createMailCheckClientMock(string $domain, bool $returnValue): MailCheckClientInterface
    {
        $mailCheckClient = $this->createMock(MailCheckClientInterface::class);
        $mailCheckClient->method('isDisposableDomain')
            ->with($domain)
            ->willReturn($returnValue);

        return $mailCheckClient;
    }

    private function createVerifyMailClientMock(string $email, bool $returnValue): VerifyMailClientInterface
    {
        $verifyMailClient = $this->createMock(VerifyMailClientInterface::class);
        $verifyMailClient->method('isDisposableEmail')
            ->with($email)
            ->willReturn($returnValue);

        return $verifyMailClient;
    }

    private function createWhoIsServiceMock(string $domain, bool $returnValue): WhoIsServiceInterface
    {
        $whoIsService = $this->createMock(WhoIsServiceInterface::class);
        $whoIsService->method('isRecentlyCreatedDomain')
            ->with($domain)
            ->willReturn($returnValue);

        return $whoIsService;
    }

    /**
     * @return array[][]
     */
    public function validEmailProvider(): array
    {
        return [
            'all_clients_return_true' => [
                'email' => 'disposable@email.com',
                'domain' => 'email.com',
                'mail_check_client_return_value' => true,
                'verify_mail_client_return_value' => true,
                'who_is_service_return_value' => true,
                'expected_checker_value' => true,
            ],
            'only_mail_check_return_true' => [
                'email' => 'disposable@email.com',
                'domain' => 'email.com',
                'mail_check_client_return_value' => true,
                'verify_mail_client_return_value' => false,
                'who_is_service_return_value' => false,
                'expected_checker_value' => true,
            ],
            'only_verify_mail_return_true' => [
                'email' => 'disposable@email.com',
                'domain' => 'email.com',
                'mail_check_client_return_value' => false,
                'verify_mail_client_return_value' => true,
                'who_is_service_return_value' => false,
                'expected_checker_value' => true,
            ],
            'onlyWhoIsServiceReturnTrue' => [
                'email' => sprintf('test@%s', WhoIsServiceMock::NEW_DOMAIN),
                'domain' => WhoIsServiceMock::NEW_DOMAIN,
                'mail_check_client_return_value' => false,
                'verify_mail_client_return_value' => false,
                'who_is_service_return_value' => true,
                'expected_checker_value' => true,
            ],
            'all_clients_return_false' => [
                'email' => 'not_disposable@email.com',
                'domain' => 'email.com',
                'mail_check_client_return_value' => false,
                'verify_mail_client_return_value' => false,
                'who_is_service_return_value' => false,
                'expected_checker_value' => false,
            ],
        ];
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function testCheckThrowExceptionIfEmailIsInvalid(string $email): void
    {
        $this->expectException(InvalidArgumentException::class);

        $checker = new DisposableEmailChecker(
            $this->createMock(MailCheckClientInterface::class),
            $this->createMock(VerifyMailClientInterface::class),
            $this->createMock(WhoIsServiceInterface::class),
        );

        $checker->isDisposableEmail($email);
    }

    public function invalidEmailProvider(): Generator
    {
        yield [''];

        yield ['someemail'];

        yield ['someemail@domain@domain.com'];

        yield ['someemail@'];

        yield ['someemail@email'];
    }
}
