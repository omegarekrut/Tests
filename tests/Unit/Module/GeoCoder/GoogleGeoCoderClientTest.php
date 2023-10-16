<?php

namespace Tests\Unit\Module\GeoCoder;

use App\Module\GeoCoder\GoogleGeoCoderClient;
use App\Util\Coordinates\Coordinates;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Tests\Unit\LoggerMock as Logger;
use Tests\Unit\TestCase;

class GoogleGeoCoderClientTest extends TestCase
{
    private Logger $logger;
    private Coordinates $coordinates;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new Logger();

        $this->coordinates = new Coordinates(
            55.0520939,
            82.874782
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->logger,
            $this->coordinates,
        );

        parent::tearDown();
    }

    public function testGeoCoderAdapterShouldReturnValidResponseContent(): void
    {
        $response = new Response(200, [], json_encode($this->getValidFullAddress()));
        $googleGeoCoderClient = $this->getGoogleGeoCoderClientInstance(
            $this->getHttpClient([
                $response,
            ])
        );

        $location = $googleGeoCoderClient->locateByCoordinates($this->coordinates);

        $this->assertEquals($this->getValidFullAddress(), $location);
    }

    /**
     * @dataProvider invalidResponseDataProvider
     */
    public function testFailGetLocationByCoordinates(int $responseStatusCode, string $body, string $logMessage): void
    {
        $response = new Response($responseStatusCode, [], $body);

        $googleGeoCoderClient = $this->getGoogleGeoCoderClientInstance(
            $this->getHttpClient([
                $response,
            ])
        );

        $this->assertEmpty($googleGeoCoderClient->locateByCoordinates($this->coordinates));

        $receivedLogMessages = $this->logger->getMessages();
        $this->assertEquals($logMessage, $receivedLogMessages[0]['message']);
        $this->assertEquals('warning', $receivedLogMessages[0]['level']);
    }

    public function testGeoCoderClientMustCatchExceptionFromHttpClient(): void
    {
        $httpClientMock = $this->getMockBuilder(Client::class)->getMock();
        $httpClientMock->method('request')->willThrowException(new Exception('Ошибка от http клиента'));

        $googleGeoCoderClient = $this->getGoogleGeoCoderClientInstance($httpClientMock);
        $googleGeoCoderClient->locateByCoordinates($this->coordinates);

        $receivedLogMessages = $this->logger->getMessages();
        $this->assertEquals('Ошибка запроса местоположения по координатам. Ошибка от http клиента', $receivedLogMessages[0]['message']);
        $this->assertEquals('warning', $receivedLogMessages[0]['level']);
    }

    private function getGoogleGeoCoderClientInstance(Client $httpClient): GoogleGeoCoderClient
    {
        return new GoogleGeoCoderClient('randomText', $httpClient, $this->logger);
    }

    /**
     * @param string[]|array $response
     *
     * @return Client
     */
    private function getHttpClient(array $response): Client
    {
        $mockHandler = new MockHandler($response);

        return new Client(['handler' => $mockHandler]);
    }

    /**
     * @return string[]|array
     */
    public function invalidResponseDataProvider(): array
    {
        return [
            'Invalid json data response' => [
                'status_code' => 200,
                'body' => 'it is not valid json encoded data',
                'exception_message' => 'Ошибка запроса местоположения по координатам. Неверный формат ответа. Невалидный json',
            ],
            'Invalid format full address. Empty array' => [
                'status_code' => 200,
                'body' => json_encode([]),
                'exception_message' => 'Ошибка запроса местоположения по координатам. Местоположение является пустым масивом. Должен быть массивом с данными.',
            ],
            'Invalid format full address. Invalid status.' => [
                'status_code' => 200,
                'body' => json_encode($this->getFullAddressWithInvalidStatus()),
                'exception_message' => 'Ошибка запроса местоположения по координатам. Неверный статус данных местоположения. Статус: NotOk. Ошибка: Bad request',
            ],
            'Invalid format full address. Empty status.' => [
                'status_code' => 200,
                'body' => json_encode($this->getFullAddressWithoutStatus()),
                'exception_message' => 'Ошибка запроса местоположения по координатам. Неверный статус данных местоположения. Статус отсутсвует в массиве.',
            ],
            'Invalid status code response 500' => [
                'status_code' => 500,
                'body' => '',
                'exception_message' => 'Ошибка запроса местоположения по координатам. Код ответа 500',
            ],
            'Invalid status code response 404' => [
                'status_code' => 404,
                'body' => '',
                'exception_message' => 'Ошибка запроса местоположения по координатам. Код ответа 404',
            ],
        ];
    }

    /**
     * @return string[]
     */
    private function getFullAddressWithInvalidStatus(): array
    {
        return [
            'status' => 'NotOk',
            'error_message' => 'Bad request',
        ];
    }

    /**
     * @return string[]
     */
    private function getFullAddressWithoutStatus(): array
    {
        return [
            'message' => 'NotOk',
        ];
    }

    /**
     * @return string[]
     */
    private function getValidFullAddress(): array
    {
        return [
            'plus_code' =>  [
                'compound_code' => '3V2F+RWM Новосибирск, Новосибирская область, Россия',
                'global_code' => '9M743V2F+RWM',
            ],
            'results' => [
                0 => [
                    'address_components' => [
                        4 => [
                            'long_name' => 'Новосибирская область',
                            'short_name' => 'Новосибирская обл.',
                            'types' => [
                                0 => 'administrative_area_level_1',
                                1 => 'political',
                            ],
                        ],
                    ],
                    'formatted_address' => 'ул. Стасова, 4, Новосибирск, Новосибирская обл., Россия, 630001',
                    'geometry' => [
                        'location' => [
                            'lat' => 55.051969,
                            'lng' => 82.8747948,
                        ],
                        'location_type' => 'ROOFTOP',
                        'viewport' => [
                            'northeast' => [
                                'lat' => 55.053317980292,
                                'lng' => 82.876143780291,
                            ],
                            'southwest' => [
                                'lat' => 55.050620019709,
                                'lng' => 82.873445819708,
                            ],
                        ],
                    ],
                ],
            ],
            'status' => 'OK',
        ];
    }
}
