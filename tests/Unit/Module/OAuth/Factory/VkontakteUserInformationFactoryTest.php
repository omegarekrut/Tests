<?php

namespace Tests\Unit\Module\OAuth\Factory;

use App\Module\OAuth\Entity\ValueObject\Gender;
use App\Module\OAuth\Factory\VkontakteUserInformationFactory;
use Tests\Unit\TestCase;

/**
 * @group oauth
 */
class VkontakteUserInformationFactoryTest extends TestCase
{
    use DecoratedPropertyAccessorTrait;

    private const RESPONSE_DATA = [
        'email' => 'email@email.com',
        'response' => [
            0 => [
                'id' => 'UUID',
                'first_name' => 'first name',
                'last_name' => 'last name',
                'city' => [
                    'title' => 'city',
                ],
                'bdate' => '27.9.2018',
                'sex' => '1',
                'photo_medium' => 'http://photo.com',
            ],
        ],
    ];

    /** @var VkontakteUserInformationFactory */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new VkontakteUserInformationFactory($this->createDecoratedPropertyAccessor());
    }

    public function testCreationOAuhUserInformation(): void
    {
        $oauthUserInformation = $this->factory->createOAuthUserInformationByData(self::RESPONSE_DATA);

        $this->assertEquals('UUID', $oauthUserInformation->getId());
        $this->assertEquals('vkontakte', $oauthUserInformation->getProvider());
        $this->assertEquals('email@email.com', $oauthUserInformation->getEmail());
        $this->assertEquals('https://vk.com/idUUID', $oauthUserInformation->getProfileUrl());
        $this->assertEquals('first name last name', $oauthUserInformation->getName());
        $this->assertEquals('city', $oauthUserInformation->getCity());
        $this->assertEquals('2018-09-27', $oauthUserInformation->getDateBirthday()->format('Y-m-d'));
        $this->assertEquals(Gender::FEMALE, $oauthUserInformation->getGender());
        $this->assertEquals('http://photo.com', $oauthUserInformation->getProfilePicture());
    }

    public function testCreationOnlyWithRequiredData(): void
    {
        $oauthUserInformation = $this->factory->createOAuthUserInformationByData([
            'response' => [
                0 => [
                    'id' => 'UUID',
                ],
            ],
        ]);

        $this->assertEquals('UUID', $oauthUserInformation->getId());
        $this->assertEquals('vkontakte', $oauthUserInformation->getProvider());
    }
}
