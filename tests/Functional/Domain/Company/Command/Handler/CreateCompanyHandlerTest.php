<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Collection\RubricCollection;
use App\Domain\Company\Command\CreateCompanyCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Rubric;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Rubric\LoadTackleShopsRubric;
use Tests\Functional\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

/**
 * @group company-create
 */
class CreateCompanyHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadTackleShopsRubric::class,
        ])->getReferenceRepository();

        /** @var User $userAdmin */
        $userAdmin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        /** @var Rubric $rubric */
        $rubric = $referenceRepository->getReference(LoadTackleShopsRubric::REFERENCE_NAME);

        $command = new CreateCompanyCommand(Uuid::uuid4(), $userAdmin);
        $command->scopeActivity = 'scopeActivity';
        $command->name = 'name';
        $command->rubrics = new RubricCollection([$rubric]);

        $this->getCommandBus()->handle($command);

        $companyRepository = $this->getEntityManager()->getRepository(Company::class);

        /** @var Company $company */
        $company = $companyRepository->find($command->id);

        $this->assertEquals($command->id, $company->getId());
        $this->assertEquals($command->name, $company->getName());
        $this->assertEquals($command->scopeActivity, $company->getScopeActivity());
    }
}
