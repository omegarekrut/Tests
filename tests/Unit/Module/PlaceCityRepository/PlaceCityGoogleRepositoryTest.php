<?php

namespace Tests\Unit\Module\PlaceCityRepository;

use App\Module\PlaceCityRepository\PlaceCityGoogleRepository;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Tests\Unit\LoggerMock;
use Tests\Unit\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class PlaceCityGoogleRepositoryTest extends TestCase
{
    private const GEOCODE_DIR = ROOT.'/tests/DataFixtures/Geocode/';
    private const VALID_RESPONSE_FILE = 'valid-response.json';
    private const ZERO_RESULTS_RESPONSE_FILE = 'zero-results-response.json';
    private const INVALID_API_KEY_FILE = 'invalid-api-key.json';
    private const CITY_NAME = 'Красноярск';
    private const EMPTY_CITY_NAME = '';
    private const INVALID_CITY_NAME = 'Не верный город';
    private const BASE_URL = 'https://maps.googleapis.com/maps/api/';

    /** @var LoggerInterface */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new LoggerMock();
    }

    protected function tearDown(): void
    {
        unset($this->logger);

        parent::tearDown();
    }

    public function testCityCannotBeFoundInInvalidApiKeyResponse(): void
    {
        $client = $this->createClientForReturnFileContent(self::GEOCODE_DIR.self::INVALID_API_KEY_FILE);

        $placeCityGoogleRepository = new PlaceCityGoogleRepository(self::BASE_URL, $this->logger, $client);
        $foundCity = $placeCityGoogleRepository->findCity(self::CITY_NAME);

        $this->assertNull($foundCity);
    }

    public function testInvalidApiKeyResponseMustCauseErrorInLogs(): void
    {
        $client = $this->createClientForReturnFileContent(self::GEOCODE_DIR.self::INVALID_API_KEY_FILE);

        $placeCityGoogleRepository = new PlaceCityGoogleRepository(self::BASE_URL, $this->logger, $client);
        $placeCityGoogleRepository->findCity(self::CITY_NAME);

        $this->assertNotEmpty($this->logger->getMessages());
    }

    public function testValidResponse(): void
    {
        $client = $this->createClientForReturnFileContent(self::GEOCODE_DIR.self::VALID_RESPONSE_FILE);

        $placeCityGoogleRepository = new PlaceCityGoogleRepository(self::BASE_URL, $this->logger, $client);
        $foundCity = $placeCityGoogleRepository->findCity(self::CITY_NAME);

        $this->assertNotEmpty($foundCity);
        $this->assertEquals(self::CITY_NAME, $foundCity->getName());
    }

    public function testRepositoryCannotFindCityInZeroResult(): void
    {
        $client = $this->createClientForReturnFileContent(self::GEOCODE_DIR.self::ZERO_RESULTS_RESPONSE_FILE);

        $placeCityGoogleRepository = new PlaceCityGoogleRepository(self::BASE_URL, $this->logger, $client);
        $foundCity = $placeCityGoogleRepository->findCity(self::INVALID_CITY_NAME);

        $this->assertEmpty($foundCity);
    }

    public function testZeroResultMustNotBeConsideredErroneous(): void
    {
        $client = $this->createClientForReturnFileContent(self::GEOCODE_DIR.self::ZERO_RESULTS_RESPONSE_FILE);

        $placeCityGoogleRepository = new PlaceCityGoogleRepository(self::BASE_URL, $this->logger, $client);
        $placeCityGoogleRepository->findCity(self::INVALID_CITY_NAME);

        $this->assertEmpty($this->logger->getMessages());
    }

    public function testEmptyResponseMustCauseErrorInLogs(): void
    {
        /** @var ClientInterface $client */
        $client = $this->createMock(ClientInterface::class);

        $placeCityGoogleRepository = new PlaceCityGoogleRepository(self::BASE_URL, $this->logger, $client);
        $foundCity = $placeCityGoogleRepository->findCity(self::EMPTY_CITY_NAME);

        $this->assertEmpty($foundCity);
        $this->assertNotEmpty($this->logger->getMessages());
    }

    private function createClientForReturnFileContent($filePath): ClientInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn(stream_for(file_get_contents($filePath)));

        $client = $this->createMock(ClientInterface::class);
        $client
            ->method('request')
            ->willReturn($response);

        return $client;
    }
}
