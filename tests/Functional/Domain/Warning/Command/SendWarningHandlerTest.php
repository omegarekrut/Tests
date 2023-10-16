<?php

namespace Tests\Functional\Domain\Warning\Service;

use App\Bridge\Xenforo\ForumApi;
use App\Bridge\Xenforo\Provider\Mock\MessageProvider;
use App\Domain\User\Entity\User;
use App\Domain\Warning\Command\Handler\SendWarningHandler;
use App\Domain\Warning\Command\SendWarningCommand;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\RepositoryTestCase;

class SendWarningHandlerTest extends RepositoryTestCase
{
    /**
     * @var ReferenceRepository
     */
    private $fixtures;

    /**
     * @var SendWarningHandler
     */
    private $sendWarningHandler;

    /**
     * @var User
     */
    private $adminUser;

    /**
     * @var User
     */
    private $warnedUser;

    private $command;
    private $forumApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            LoadAdminUser::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->forumApi = new ForumApi();
        $this->forumApi->addProvider(new MessageProvider());

        $this->adminUser = $this->fixtures->getReference(LoadAdminUser::REFERENCE_NAME);
        $this->adminUser->setForumUserId(1);

        $this->warnedUser = $this->fixtures->getReference(LoadTestUser::USER_TEST);
        $this->warnedUser->setForumUserId(2);

        $this->sendWarningHandler = new SendWarningHandler($this->forumApi);
        $this->command = new SendWarningCommand($this->adminUser, $this->warnedUser);
        $this->command->text = 'warning text';
    }

    protected function tearDown(): void
    {
        unset(
            $this->fixtures,
            $this->forumApi,
            $this->adminUser,
            $this->warnedUser,
            $this->sendWarningHandler,
            $this->command
        );

        parent::tearDown();
    }

    public function testSuccessWarnSentToForum(): void
    {
        try {
            $this->sendWarningHandler->handle($this->command);

            $this->assertTrue($this->forumApi->message()->isSendWarningCalled(), 'Warning message is sent');
        } finally {
            $this->forumApi->message()->flush();
        }
    }
}
