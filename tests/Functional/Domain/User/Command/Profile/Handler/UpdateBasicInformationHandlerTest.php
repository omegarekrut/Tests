<?php

namespace Tests\Functional\Domain\User\Command\Profile\Handler;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\Mock\UserProvider;
use App\Domain\User\Command\Profile\UpdateBasicInformationCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Gender;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class UpdateBasicInformationHandlerTest extends TestCase
{
    /** @var User */
    private $user;
    /** @var ForumApiInterface */
    private $forumApi;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->forumApi = $this->getContainer()->get(ForumApiInterface::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->command,
            $this->forumApi
        );

        parent::tearDown();
    }

    public function testUserMustBeUpdatedByCommandData(): void
    {
        $command = new UpdateBasicInformationCommand($this->user);

        $command->login = $this->getFaker()->unique()->userName;
        $command->email = 'test@gmail.com';
        $command->name = $this->getFaker()->realText(100);
        $command->gender = (string) Gender::MALE();
        $command->birthdate = $this->getFaker()->dateTime();
        $command->cityName = 'Новосибирск';
        $command->cityCountry = 'Россия';

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->login, $this->user->getLogin());
        $this->assertEquals($command->email, $this->user->getEmailAddress());
        $this->assertEquals($command->name, $this->user->getName());
        $this->assertEquals($command->gender, $this->user->getGender());
        $this->assertEquals($command->birthdate, $this->user->getBirthdate());
        $this->assertEquals($command->cityName, $this->user->getCity()->getName());
        $this->assertEquals($command->cityCountry, $this->user->getCity()->getCountry());
    }

    public function testUpdatedUserShouldBeAlsoUpdatedInForum(): void
    {
        $command = new UpdateBasicInformationCommand($this->user);
        $this->getCommandBus()->handle($command);

        /** @var UserProvider $forumUserProvider */
        $forumUserProvider = $this->forumApi->user();

        $this->assertTrue($forumUserProvider->isUserUpdated($this->user));
    }

    public function testAfterUpdatingEmailUserMustReceiveConfirmationLink(): void
    {
        $command = new UpdateBasicInformationCommand($this->user);
        $command->email = 'updated'.$this->user->getEmailAddress();

        $this->getCommandBus()->handle($command);

        $mailWithConfirmationLink = $this->loadLastEmailMessage();

        $this->assertNotEmpty($mailWithConfirmationLink);
        $this->assertStringContainsString($command->email, $mailWithConfirmationLink);
        $this->assertStringContainsString('Для завершения регистрации, пожалуйста, пройдите по ссылке ниже', $mailWithConfirmationLink);
    }
}
