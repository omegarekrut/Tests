<?php

namespace Tests\Unit\Domain\User\Command\UserRegistration\Factory;

use App\Domain\User\Command\UserRegistration\Factory\UserRegisterThroughOAuthCommandFactory;
use App\Module\OAuth\Entity\OAuthUserInformation;
use App\Module\OAuth\Entity\ValueObject\Gender;
use App\Module\OAuth\Storage\SessionOAuthUserStorage;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group oauth
 * @group registration
 */
class UserRegisterThroughOAuthCommandFactoryTest extends TestCase
{
    private const ID = '';
    private const EMAIL = 'email@email.com';
    private const NAME = 'name';
    private const CITY = 'city';
    private const BIRTHDAY = '27.09.2018';
    private const GENDER = Gender::FEMALE;
    private const PROVIDER_NAME = 'providerName';
    private const PROFILE_URL = 'http://foo.bar';

    public function testCreateCommandFromUserResponse(): void
    {
        $oauthUserInformation = $this->createOAuthUserInformation([
            'getId' => self::ID,
            'getEmail' => self::EMAIL,
            'getName' => self::NAME,
            'getCity' => self::CITY,
            'getDateBirthday' => Carbon::parse(self::BIRTHDAY),
            'getGender' => new Gender(self::GENDER),
            'getProvider' => self::PROVIDER_NAME,
            'getProfileUrl' => new Uri(self::PROFILE_URL),
        ]);
        $userResponseStorage = $this->createUserInformationStorage($oauthUserInformation);

        $factory = new UserRegisterThroughOAuthCommandFactory($userResponseStorage);
        $command = $factory->createCommand();

        $this->assertEquals(self::EMAIL, $command->email);
        $this->assertTrue($command->isTrustedEmail());
        $this->assertEquals(self::NAME, $command->name);
        $this->assertEquals(self::CITY, $command->city);
        $this->assertEquals(self::BIRTHDAY, $command->dateBirthday->format('d.m.Y'));
        $this->assertEquals(self::GENDER, $command->gender);
        $this->assertEquals(self::PROVIDER_NAME, $command->providerName);
        $this->assertEquals(self::ID, $command->providerUuid);
        $this->assertEquals(self::PROFILE_URL, $command->profileUrl);
    }

    private function createUserInformationStorage(?OAuthUserInformation $oauthUserInformation = null): SessionOAuthUserStorage
    {
        $stub = $this->createMock(SessionOAuthUserStorage::class);
        $stub
            ->expects($this->once())
            ->method('getUserInformation')
            ->willReturn($oauthUserInformation);

        return $stub;
    }

    private function createOAuthUserInformation(array $getters): OAuthUserInformation
    {
        $stub = $this->createMock(OAuthUserInformation::class);

        foreach ($getters as $getter => $value) {
            $stub
                ->expects($this->any())
                ->method($getter)
                ->willReturn($value);
        }

        return $stub;
    }
}
