<?php

namespace Tests\Unit\Domain\CompanyLetter\Command\Newsletter\Handler;

use App\Domain\CompanyLetter\Command\Newsletter\CreateAndSendCompanyLetterCommand;
use App\Domain\CompanyLetter\Command\Newsletter\CreateCompanyLetterCommand;
use App\Domain\CompanyLetter\Command\Newsletter\Handler\CreateAndSendCompanyLetterHandler;
use App\Domain\CompanyLetter\Command\Newsletter\SendCompaniesLettersCommand;
use App\Domain\CompanyLetter\Entity\ValueObject\CompanyLetterPeriod;
use Exception;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;

class CreateAndSendCompanyLetterHandlerTest extends TestCase
{
    private CreateAndSendCompanyLetterCommand $command;
    private CommandBusMock $commandBus;
    private CreateAndSendCompanyLetterHandler $createAndSendCompanyLetterHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = new CommandBusMock();

        $this->command = new CreateAndSendCompanyLetterCommand(
            CompanyLetterPeriod::createLastAccessibleCompanyLetterPeriod()
        );

        $this->createAndSendCompanyLetterHandler = new CreateAndSendCompanyLetterHandler();
        $this->createAndSendCompanyLetterHandler->setCommandBus($this->commandBus);
    }

    protected function tearDown(): void
    {
        unset(
            $this->commandBus,
            $this->command,
            $this->createAndSendCompanyLetterHandler,
        );

        parent::tearDown();
    }

    /**
     * @throws Exception
     */
    public function testAfterHandlingCommandMustBeHandledCreateCommandAndSendCommand(): void
    {
        $this->createAndSendCompanyLetterHandler->handle($this->command);

        $this->assertTrue($this->commandBus->isHandled(CreateCompanyLetterCommand::class));
        $this->assertTrue($this->commandBus->isHandled(SendCompaniesLettersCommand::class));
    }
}
