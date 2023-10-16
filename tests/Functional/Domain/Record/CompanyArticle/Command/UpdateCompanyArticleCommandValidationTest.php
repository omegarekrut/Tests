<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\Command;

use App\Domain\Record\CompanyArticle\Command\UpdateCompanyArticleCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadPaidReservoirsCompanyArticle;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\ValidationTestCase;

/**
 * @group company
 */
class UpdateCompanyArticleCommandValidationTest extends ValidationTestCase
{
    private UpdateCompanyArticleCommand $command;
    private User $authorIsNotOwnerCompany;
    private CompanyArticle $companyArticle;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadUserWithAvatar::class,
            LoadPaidReservoirsCompanyArticle::class,
        ])->getReferenceRepository();

        $this->companyArticle = $referenceRepository->getReference(LoadPaidReservoirsCompanyArticle::REFERENCE_NAME);
        $this->authorIsNotOwnerCompany = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);

        $this->command = new UpdateCompanyArticleCommand($this->companyArticle);
        $this->command->title = 'New company article title created';
        $this->command->text = 'New company article text';
        $this->command->images = new ImageCollection([new Image('image.jpg')]);
        $this->command->youtubeVideoUrls = [
            'https://www.youtube.com/watch?v=-964sSBviK0&ab_channel=Jamie%27sDesign',
        ];
    }

    protected function tearDown(): void
    {
        unset(
            $this->command,
            $this->authorIsNotOwnerCompany,
            $this->companyArticle,
        );

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->command->title = null;
        $this->command->text = null;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Это поле обязательно для заполнения.');
        $this->assertFieldInvalid('text', 'Это поле обязательно для заполнения.');
    }

    public function testInvalidLengthFields(): void
    {
        $this->command->title = $this->getFaker()->realText(300);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Длина не должна превышать 255 символов.');
    }

    public function testContainTooMuchUpperCase(): void
    {
        $fakeText = mb_strtoupper($this->getFaker()->realText(50));
        $this->command->title = $fakeText;
        $this->command->text = $fakeText;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('title', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
        $this->assertFieldInvalid('text', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testVideoUrlsFieldContainsNotYoutubeUrl(): void
    {
        $this->command->youtubeVideoUrls = [
            $this->getFaker()->url,
        ];

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('youtubeVideoUrls[0]', 'Поддерживаются видео только с youtube.');
    }

    public function testVideoUrlsFieldContainsWrongVideoUrl(): void
    {
        $this->command->youtubeVideoUrls = [
            'https://www.youtube.com/watch?v=WRONGYOUTUBE',
        ];

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('youtubeVideoUrls[0]', 'Видео не содержит iframe');
    }

    public function testContainPastDate(): void
    {
        $this->command->publishAt = Carbon::now()->subWeek();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('publishAt', 'Указанная дата уже прошла.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->publishAt = Carbon::now()->addWeek();

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
