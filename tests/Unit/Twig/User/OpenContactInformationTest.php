<?php

namespace Tests\Unit\Twig\User;

use App\Domain\User\Collection\LinkedAccountCollection;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\Email;
use App\Twig\User\OpenContactInformation;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class OpenContactInformationTest extends TestCase
{
    /**
     * @param int[] $linkedAccounts
     *
     * @dataProvider usersWithContactInformation
     */
    public function testFilter(array $linkedAccounts, bool $isShowedSocial, string $emailAddress, bool $isEmailShowed, bool $expectedStatus): void
    {
        $user = $this->createConfiguredMock(User::class, [
            'getLinkedAccounts' => new LinkedAccountCollection($linkedAccounts),
            'isShowedSocial' => $isShowedSocial,
            'getEmailAddress' => $emailAddress,
            'getEmail' => $this->createEmailMock($isEmailShowed),
        ]);

        $filter = new OpenContactInformation();

        $this->assertEquals($expectedStatus, $filter($user));
    }

    public function usersWithContactInformation(): \Generator
    {
        yield [
            'linkedAccounts' => [],
            'isShowedSocial' => false,
            'emailAddress' => '',
            'isEmailShowed' => false,
            'expectedStatus' => false,
        ];

        yield [
            'linkedAccounts' => [],
            'isShowedSocial' => false,
            'emailAddress' => '',
            'isEmailShowed' => true,
            'expectedStatus' => false,
        ];

        yield [
            'linkedAccounts' => [],
            'isShowedSocial' => false,
            'emailAddress' => 'some@email.com',
            'isEmailShowed' => false,
            'expectedStatus' => false,
        ];

        yield [
            'linkedAccounts' => [],
            'isShowedSocial' => false,
            'emailAddress' => 'some@email.com',
            'isEmailShowed' => true,
            'expectedStatus' => true,
        ];

        yield [
            'linkedAccounts' => [],
            'isShowedSocial' => true,
            'emailAddress' => '',
            'isEmailShowed' => false,
            'expectedStatus' => false,
        ];

        yield [
            'linkedAccounts' => [1],
            'isShowedSocial' => false,
            'emailAddress' => '',
            'isEmailShowed' => false,
            'expectedStatus' => false,
        ];

        yield [
            'linkedAccounts' => [1],
            'isShowedSocial' => true,
            'emailAddress' => '',
            'isEmailShowed' => false,
            'expectedStatus' => true,
        ];
    }

    private function createEmailMock(bool $isShowed): Email
    {
        return $this->createConfiguredMock(Email::class, ['isShowed' => $isShowed]);
    }
}
