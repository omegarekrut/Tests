<?php

namespace Tests\Unit\Domain\Seo\Factory;

use App\Domain\Seo\Factory\UserProfileSeoFactory;
use App\Domain\User\Entity\ValueObject\FishingInformation;
use App\Domain\User\View\UserProfile\UserProfileView;
use Carbon\Carbon;
use Tests\Unit\TestCase;

class UserProfileSeoFactoryTest extends TestCase
{
    private UserProfileSeoFactory $userProfileSeoFactory;

    public static function getDataForCreateTitle(): \Generator
    {
        $profileWithName = new UserProfileView();
        $profileWithName->name = 'Иван Иванов';
        $profileWithName->username = 'ivan42';

        yield 'with name' => [
            'Иван Иванов, он же ivan42',
            $profileWithName,
        ];

        $profileWithoutName = new UserProfileView();
        $profileWithoutName->username = 'ivan42';

        yield 'without name' => [
            'Профиль пользователя ivan42.',
            $profileWithoutName,
        ];
    }

    public static function getDataForCreateDescription(): \Generator
    {
        $profileWithAboutMeInFishingInformation = new UserProfileView();
        $profileWithAboutMeInFishingInformation->fishingInformation = new FishingInformation([], 'Лучший в мире рыбак');

        yield 'with about me fishing information' => [
            'Лучший в мире рыбак',
            $profileWithAboutMeInFishingInformation,
        ];

        $fishingInformationWithFishingForYou = new FishingInformation([], null, ['Хобби', 'Спорт']);

        $profileWithFishingForYouInFishingInformation = new UserProfileView();
        $profileWithFishingForYouInFishingInformation->username = 'ivan42';
        $profileWithFishingForYouInFishingInformation->createdAt = Carbon::create(2023, 5, 9);
        $profileWithFishingForYouInFishingInformation->fishingInformation = $fishingInformationWithFishingForYou;

        yield 'without about me, but with fishing for you fishing information' => [
            'Информация о пользователе ivan42 и его записях на сайте FishingSib.ru. ivan42 зарегистрирован 9 мая 2023. Рыбалка в жизни Хобби, Спорт.',
            $profileWithFishingForYouInFishingInformation,
        ];

        $profileWithoutFishingInformation = new UserProfileView();
        $profileWithoutFishingInformation->username = 'ivan42';
        $profileWithoutFishingInformation->createdAt = Carbon::create(2023, 5, 9);
        $profileWithoutFishingInformation->fishingInformation = new FishingInformation();

        yield 'with empty fishing information' => [
            'Информация о пользователе ivan42 и его записях на сайте FishingSib.ru. ivan42 зарегистрирован 9 мая 2023.',
            $profileWithoutFishingInformation,
        ];
    }

    /**
     * @dataProvider getDataForCreateTitle
     */
    public function testCreateTitle(string $expectedTitle, UserProfileView $userProfile): void
    {
        $title = $this->userProfileSeoFactory->createTitleForUserProfilePage($userProfile);

        $this->assertEquals($expectedTitle, $title);
    }

    /**
     * @dataProvider getDataForCreateDescription
     */
    public function testCreateDescription(string $expectedDescription, UserProfileView $userProfile): void
    {
        $description = $this->userProfileSeoFactory->createDescriptionForUserProfilePage($userProfile);

        $this->assertEquals($expectedDescription, $description);
    }

    protected function setUp(): void
    {
        $this->userProfileSeoFactory = new UserProfileSeoFactory();
    }
}
