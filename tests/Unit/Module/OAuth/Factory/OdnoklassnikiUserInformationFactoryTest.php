<?php

namespace Tests\Unit\Module\OAuth\Factory;

use App\Module\OAuth\Entity\ValueObject\Gender;
use App\Module\OAuth\Factory\OdnoklassnikiUserInformationFactory;
use Tests\Unit\TestCase;

/**
 * @group oauth
 */
class OdnoklassnikiUserInformationFactoryTest extends TestCase
{
    use DecoratedPropertyAccessorTrait;

    private const RESPONSE_DATA = [
        'email' => 'email@email.com',
        'uid' => 'UUID',
        'birthday' => '2018-09-27',
        'first_name' => 'first name',
        'last_name' => 'last name',
        'gender' => 'female',
        'location' => [
            'city' => 'city',
        ],
        'pic_3' => 'http://photo.com',
    ];

    /** @var OdnoklassnikiUserInformationFactory */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new OdnoklassnikiUserInformationFactory($this->createDecoratedPropertyAccessor());
    }

    public function testCreationOAuhUserInformation(): void
    {
        $oauthUserInformation = $this->factory->createOAuthUserInformationByData(self::RESPONSE_DATA);

        $this->assertEquals('UUID', $oauthUserInformation->getId());
        $this->assertEquals('odnoklassniki', $oauthUserInformation->getProvider());
        $this->assertEquals('email@email.com', $oauthUserInformation->getEmail());
        $this->assertEquals('https://ok.ru/profile/UUID', $oauthUserInformation->getProfileUrl());
        $this->assertEquals('first name last name', $oauthUserInformation->getName());
        $this->assertEquals('city', $oauthUserInformation->getCity());
        $this->assertEquals('2018-09-27', $oauthUserInformation->getDateBirthday()->format('Y-m-d'));
        $this->assertEquals(Gender::FEMALE, $oauthUserInformation->getGender());
        $this->assertEquals('http://photo.com', $oauthUserInformation->getProfilePicture());
    }

    public function testCreationOnlyWithRequiredData(): void
    {
        $oauthUserInformation = $this->factory->createOAuthUserInformationByData([
            'uid' => 'UUID',
        ]);

        $this->assertEquals('UUID', $oauthUserInformation->getId());
        $this->assertEquals('odnoklassniki', $oauthUserInformation->getProvider());
    }
}
