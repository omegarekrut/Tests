<?php

namespace Tests\Controller;

use App\Util\FrameworkBundle\Client;
use LogicException;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\Traits\FileSystemTrait;

abstract class TestCase extends WebTestCase
{
    use FileSystemTrait;

    private Client $browser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->browser = $this->createClient();

        /** @todo delete after the schema update strategy appears in liip bundle */
        $this->setExcludedDoctrineTables(['fake_table_name_to_ban_the_consciousness_of_the_new_scheme_only_update']);
    }

    protected function tearDown(): void
    {
        unset($this->browser);

        parent::tearDown();
    }

    final protected function getBrowser(): Client
    {
        return $this->browser;
    }

    /** {@inheritDoc} */
    protected static function createClient(array $options = [], array $server = [])
    {
        $kernel = static::bootKernel($options);

        try {
            $client = $kernel->getContainer()->get('test.client');
        } catch (ServiceNotFoundException $e) {
            if (class_exists(Client::class)) {
                throw new LogicException('You cannot create the client used in functional tests if the "framework.test" config is not set to true.');
            }
            throw new LogicException('You cannot create the client used in functional tests if the BrowserKit component is not available. Try running "composer require symfony/browser-kit".');
        }

        if (getenv('SCHEMA') === 'https') {
            $client->setServerParameter('HTTPS', true);
        }

        // @see Removed reset server variables from container

        return $client;
    }

    final protected function clearDatabase(): void
    {
        $this->loadFixtures([]);
    }

    /**
     * @template T
     *
     * @param class-string<SingleReferenceFixtureInterface> $singleFixtureClass
     * @param class-string<T> $expectedReferenceClass
     *
     * @return T
     */
    final protected function loadFixture(string $singleFixtureClass, string $expectedReferenceClass): object
    {
        $referenceRepository = $this->loadFixtures([$singleFixtureClass], true)->getReferenceRepository();

        $reference = $referenceRepository->getReference($singleFixtureClass::getReferenceName());
        assert($reference instanceof $expectedReferenceClass);

        return $reference;
    }

    /**
     * @todo extract to Client?
     */
    public function assertSeeAlertInPageContent(string $type, string $message, string $pageContent): void
    {
        $alerts = $this->findAlertsInPageSource($pageContent);
        $actualAlert = null;

        foreach ($alerts as $alert) {
            if ($alert['level'] !== $type || $alert['message'] !== $message) {
                continue;
            }

            $actualAlert = $alert;

            break;
        }

        $failedMessage = sprintf('Alert not found in %s.', json_encode($alerts));

        $this->assertNotEmpty($actualAlert, $failedMessage);
    }

    private function findAlertsInPageSource(string $pageSource): array
    {
        $matching = [];

        if (!preg_match_all('/var alertsBag = (\[)(.*)(\])/i', $pageSource, $matching, PREG_SET_ORDER)) {
            return [];
        }

        $lastAlertsDefinitionMatching = end($matching);

        return json_decode($lastAlertsDefinitionMatching[1].$lastAlertsDefinitionMatching[2].$lastAlertsDefinitionMatching[3], true);
    }
}
