<?php

namespace Tests\Functional\Domain\Ban\Ip;

use App\Domain\Ban\Entity\BanIp;
use App\Domain\Ban\Repository\BanIpRepository;
use App\Domain\Ban\Search\BanIpSearchData;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use IPTools\Range;
use Tests\DataFixtures\ORM\Ban\LoadBanIp;
use Tests\Functional\RepositoryTestCase;

/**
 * @group ban
 */
class BanIpRepositoryTest extends RepositoryTestCase
{
    /**
     * @var BanIpRepository
     */
    private $repository;

    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadBanIp::class,
        ])->getReferenceRepository();

        $this->repository = $this->getRepository(BanIp::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->repository
        );

        parent::tearDown();
    }

    public function testFindAllBySearchDataWithIp(): void
    {
        /** @var BanIp $banIp */
        $banIp = $this->referenceRepository->getReference(LoadBanIp::BAN_IP);

        $searchData = new BanIpSearchData();
        $searchData->ip = $this->chooseIpFromRange($banIp->getIpRange());

        $list = $this->repository->findAllBySearchData($searchData)->getQuery()->execute();
        $this->assertIsArray($list);
        $this->assertContains($banIp, $list);
    }

    public function testFindAllBySearchDataWithPartIp(): void
    {
        /** @var BanIp $banIp */
        $banIp = $this->referenceRepository->getReference(LoadBanIp::BAN_IP_EXPIRED);

        $searchData = new BanIpSearchData();
        $searchData->ip = $this->chooseIpFromRange($banIp->getIpRange());
        $searchData->ip = substr($searchData->ip, 0, 5);

        $list = $this->repository->findAllBySearchData($searchData)->getQuery()->execute();
        $this->assertIsArray($list);
        $this->assertContains($banIp, $list);
    }

    public function testFindAllBySearchDataWithCause(): void
    {
        /** @var BanIp $banIp */
        $banIp = $this->referenceRepository->getReference(LoadBanIp::BAN_IP);

        $searchData = new BanIpSearchData();
        $searchData->cause = $banIp->getCause();

        $list = $this->repository->findAllBySearchData($searchData)->getQuery()->execute();
        $this->assertIsArray($list);
        $this->assertContains($banIp, $list);
    }

    public function testGetBannedListIps(): void
    {
        $list = $this->repository->getBannedListIps();
        $this->assertIsArray($list);
        $this->assertIsString($list[0]);
    }

    public function testFindActiveByIp(): void
    {
        $banIp = $this->referenceRepository->getReference(LoadBanIp::BAN_IP);
        $actualBanIp = $this->repository->findActiveByIp($this->chooseIpFromRange($banIp->getIpRange()));

        $this->assertEquals($banIp, $actualBanIp);

        $expiredBanIp = $this->referenceRepository->getReference(LoadBanIp::BAN_IP_EXPIRED);
        $actualBanIp = $this->repository->findActiveByIp($this->chooseIpFromRange($expiredBanIp->getIpRange()));

        $this->assertEmpty($actualBanIp);
    }

    private function chooseIpFromRange(string $ipRange): string
    {
        $ipRange = Range::parse($ipRange);

        if (count($ipRange) > 1) {
            return (string) $ipRange->getFirstIP()->next();
        }

        return (string) $ipRange->getFirstIP();
    }
}
