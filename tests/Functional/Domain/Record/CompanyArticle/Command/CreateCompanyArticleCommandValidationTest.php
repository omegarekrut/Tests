<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\Command;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Command\CreateCompanyArticleCommand;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleForSemanticLinks;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\ValidationTestCase;

/**
 * @group company
 */
class CreateCompanyArticleCommandValidationTest extends ValidationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();
    }

    public function testNotBlankFields(): void
    {
        $command = $this->createCommand();
        $command->title = null;
        $command->text = null;

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('title', 'Это поле обязательно для заполнения.');
        $this->assertFieldInvalid('text', 'Это поле обязательно для заполнения.');
    }

    public function testInvalidLengthFields(): void
    {
        $command = $this->createCommand();

        $command->title = $this->getFaker()->realText(300);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('title', 'Длина не должна превышать 255 символов.');
    }

    public function testContainTooMuchUpperCase(): void
    {
        $command = $this->createCommand();

        $fakeText = mb_strtoupper($this->getFaker()->realText(50));
        $command->title = $fakeText;
        $command->text = $fakeText;

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('title', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
        $this->assertFieldInvalid('text', 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testVideoUrlsFieldContainsNotYoutubeUrl(): void
    {
        $command = $this->createCommand();

        $command->youtubeVideoUrls = [
            $this->getFaker()->url,
        ];

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('youtubeVideoUrls[0]', 'Поддерживаются видео только с youtube.');
    }

    public function testVideoUrlsFieldContainsWrongVideoUrl(): void
    {
        $command = $this->createCommand();

        $command->youtubeVideoUrls = [
            'https://www.youtube.com/watch?v=WRONGYOUTUBE',
        ];

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('youtubeVideoUrls[0]', 'Видео не содержит iframe');
    }

    public function testAuthorIsNotOwnerCompany(): void
    {
        $company = $this->loadCompanyWithoutSubscription();

        $command = $this->createCommandWithCompany($company);

        $authorIsNotOwnerCompany = $this->loadUser();

        $command->author = $authorIsNotOwnerCompany;

        $this->getValidator()->validate($command);

        $validationErrors = $this->getValidator()->getLastErrors();

        foreach ($validationErrors as $error) {
            $this->assertEquals(
                sprintf('Вам не разрешено публиковать записи от имени компании %s.', $command->company->getName()),
                $error->getMessage()
            );
        }
    }

    public function testContainPastDate(): void
    {
        $command = $this->createCommand();

        $command->publishAt = Carbon::now()->subWeek();

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('publishAt', 'Указанная дата уже прошла.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $company = $this->loadCompanyWithoutSubscription();

        $command = $this->createCommandWithCompany($company);

        $command->publishAt = Carbon::now()->addWeek();

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    public function testCompanyMustNotExceedLimitOfPublications(): void
    {
        $company = $this->loadCompanyWithArticle();

        $command = $this->createCommandWithCompany($company);
        $command->publishAt = Carbon::now()->addDays(30)->subMinute();

        $this->getValidator()->validate($command);

        $errors = $this->getValidator()->getLastErrors();
        $this->assertEquals('Вы превысили лимит публикаций.', $errors[0]->getMessage());
    }

    private function createCommand(): CreateCompanyArticleCommand
    {
        $command = new CreateCompanyArticleCommand();

        $command->title = 'Company article title created';
        $command->text = 'Company article text';
        $command->images = new ImageCollection([new Image('image.jpg')]);
        $command->youtubeVideoUrls = [
            'https://www.youtube.com/watch?v=-964sSBviK0&ab_channel=Jamie%27sDesign',
        ];

        return $command;
    }

    private function createCommandWithCompany(Company $company): CreateCompanyArticleCommand
    {
        $command = new CreateCompanyArticleCommand();

        $command->author = $company->getOwner();
        $command->company = $company;
        $command->title = 'Company article title created';
        $command->text = 'Company article text';
        $command->images = new ImageCollection([new Image('image.jpg')]);
        $command->youtubeVideoUrls = [
            'https://www.youtube.com/watch?v=-964sSBviK0&ab_channel=Jamie%27sDesign',
        ];

        return $command;
    }

    private function loadCompanyWithoutSubscription(): Company
    {
        return $this->loadFixture(LoadCompanyWithOwner::class, Company::class);
    }

    private function loadCompanyWithArticle(): Company
    {
        $referenceRepository = $this->loadFixtures([LoadAquaMotorcycleShopsCompany::class, LoadCompanyArticleForSemanticLinks::class])->getReferenceRepository();

        $companyWithArticles = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::getReferenceName());
        assert($companyWithArticles instanceof Company);

        $companyWithArticles->getArticles()->first()->rewritePublishAt(Carbon::now());

        return $companyWithArticles;
    }

    private function loadUser(): User
    {
        return $this->loadFixture(LoadUserWithAvatar::class, User::class);
    }
}
