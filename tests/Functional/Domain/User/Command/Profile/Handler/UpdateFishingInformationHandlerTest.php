<?php

namespace Tests\Functional\Domain\User\Command\Profile\Handler;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\Mock\UserProvider;
use App\Domain\User\Command\Profile\UpdateFishingInformationCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\FishingInformation;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class UpdateFishingInformationHandlerTest extends TestCase
{
    /** @var User */
    private $user;
    /** @var ForumApiInterface */
    private $forumApi;
    /** @var UpdateFishingInformationCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->forumApi = $this->getContainer()->get(ForumApiInterface::class);
        $this->forumApi->addProvider(new UserProvider());

        $this->command = new UpdateFishingInformationCommand($this->user);
        $this->command->fishingForYou = [FishingInformation::FISHING_FOR_YOU_CHOICE[0]];
        $this->command->fishingTypes = [FishingInformation::FISHING_TYPES[0]];
        $this->command->fishingTime = FishingInformation::FISHING_TIME[0];
        $this->command->aboutMe = $this->getFaker()->text(20);
        $this->command->watercraft = $this->getFaker()->text(20);
        $this->command->haveWatercraft = true;
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->forumApi,
            $this->command
        );

        parent::tearDown();
    }

    public function testUserFishingInformationMustBeUpdatedByCommandData(): void
    {
        $this->getCommandBus()->handle($this->command);

        $this->assertEquals($this->command->fishingForYou, $this->user->getFishingInformation()->getFishingForYou());
        $this->assertEquals($this->command->fishingTypes, $this->user->getFishingInformation()->getFishingTypes());
        $this->assertEquals($this->command->fishingTime, $this->user->getFishingInformation()->getFishingTime());
        $this->assertEquals($this->command->aboutMe, $this->user->getFishingInformation()->getAboutMe());
        $this->assertEquals($this->command->watercraft, $this->user->getFishingInformation()->getWatercraft());
    }

    public function testUserFishingInformationMustBeUpdatedByCommandDataWithEmptyWatercraft(): void
    {
        $this->command->haveWatercraft = false;

        $this->getCommandBus()->handle($this->command);

        $this->assertEquals($this->command->fishingForYou, $this->user->getFishingInformation()->getFishingForYou());
        $this->assertEquals($this->command->fishingTypes, $this->user->getFishingInformation()->getFishingTypes());
        $this->assertEquals($this->command->fishingTime, $this->user->getFishingInformation()->getFishingTime());
        $this->assertEquals($this->command->aboutMe, $this->user->getFishingInformation()->getAboutMe());
        $this->assertEmpty($this->user->getFishingInformation()->getWatercraft());
    }

    public function testUserInformationAlsoMustBeUpdatedInForum(): void
    {
        $this->getCommandBus()->handle($this->command);

        $this->assertTrue($this->forumApi->user()->isUserUpdated($this->user));
    }
}
