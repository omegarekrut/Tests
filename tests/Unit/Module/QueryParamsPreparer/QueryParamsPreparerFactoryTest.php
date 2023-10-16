<?php

namespace Tests\Unit\Module\QueryParamsPreparer;

use App\Module\QueryParamsPreparer\QueryParamsPreparerFactory;
use Tests\Unit\TestCase;

class QueryParamsPreparerFactoryTest extends TestCase
{
    /**
     * @inheritDoc
     *
     * @dataProvider getParams
     */
    public function testPreparedQueryParams(array $originParams, array $expectedParams): void
    {
        $queryParamsPreparerFactory = new QueryParamsPreparerFactory();

        $this->assertEquals($expectedParams, $queryParamsPreparerFactory->getPreparedParams($originParams));
    }

    /**
     * @return mixed[]
     */
    public function getParams(): array
    {
        return [
            'validParams' => [
                [
                    'firstParam' => 'foo',
                    'secondParam' => 'bar',
                ],
                [
                    'firstParam' => 'foo',
                    'secondParam' => 'bar',
                ],
            ],
            'paramsWithSpacesInBeginning' => [
                [
                    'firstParam' => '   foo',
                    'secondParam' => '%20%20%20bar',
                    'thirdParam' => '+++baz',
                ],
                [
                    'firstParam' => 'foo',
                    'secondParam' => 'bar',
                    'thirdParam' => 'baz',
                ],
            ],
            'paramsWithSpacesInTheEnd' => [
                [
                    'firstParam' => 'foo   ',
                    'secondParam' => 'bar%20%20%20',
                    'thirdParam' => 'baz+++',
                ],
                [
                    'firstParam' => 'foo',
                    'secondParam' => 'bar',
                    'thirdParam' => 'baz',
                ],
            ],
            'arrayParams' => [
                [
                    'firstArrayParam' => [
                        'firstParam' => '   foo',
                        'secondParam' => 'bar   ',
                        'thirdParam' => '   baz   ',
                    ],
                ],
                [
                    'firstArrayParam' => [
                        'firstParam' => 'foo',
                        'secondParam' => 'bar',
                        'thirdParam' => 'baz',
                    ],
                ],
            ],
        ];
    }
}
