<?php

namespace Tests\Unit\Module\OAuth\Factory;

use App\Module\OAuth\Entity\ValueObject\Gender;
use App\Module\OAuth\Factory\YandexUserInformationFactory;
use Tests\Unit\TestCase;

/**
 * @group oauth
 */
class YandexUserInformationFactoryTest extends TestCase
{
    use DecoratedPropertyAccessorTrait;

    private const RESPONSE_DATA = [
        'default_email' => 'email@email.com',
        'id' => 'UUID',
        'first_name' => 'first name',
        'last_name' => 'last name',
        'birthday' => '27.9.2018',
        'sex' => 'female',
        'is_avatar_empty' => false,
        'default_avatar_id' => 'avatar',
    ];

    /** @var YandexUserInformationFactory */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new YandexUserInformationFactory($this->createDecoratedPropertyAccessor());
    }

    public function testCreationOAuhUserInformation(): void
    {
        $oauthUserInformation = $this->factory->createOAuthUserInformationByData(self::RESPONSE_DATA);

        $this->assertEquals('UUID', $oauthUserInformation->getId());
        $this->assertEquals('yandex', $oauthUserInformation->getProvider());
        $this->assertEquals('email@email.com', $oauthUserInformation->getEmail());
        $this->assertEquals(null, $oauthUserInformation->getProfileUrl());
        $this->assertEquals('first name last name', $oauthUserInformation->getName());
        $this->assertEquals(null, $oauthUserInformation->getCity());
        $this->assertEquals('2018-09-27', $oauthUserInformation->getDateBirthday()->format('Y-m-d'));
        $this->assertEquals(Gender::FEMALE, $oauthUserInformation->getGender());
        $this->assertEquals('https://avatars.yandex.net/get-yapic/avatar/islands-retina-50', $oauthUserInformation->getProfilePicture());
    }

    public function testCreationOnlyWithRequiredData(): void
    {
        $oauthUserInformation = $this->factory->createOAuthUserInformationByData([
            'id' => 'UUID'
        ]);

        $this->assertEquals('UUID', $oauthUserInformation->getId());
        $this->assertEquals('yandex', $oauthUserInformation->getProvider());
    }
}
