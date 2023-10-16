<?php

namespace Tests\Traits;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\RelayBuilder;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;

trait RelayTrait
{
    /**
     * Получить обработанный ответ через список Middlewares
     *
     * @param array $middlewares список Middlewares
     * @param ServerRequestInterface|null $request запрос, будет создан в случае null
     * @param ResponseInterface|null $response ответ, будет создан в случая null
     *
     * @return ResponseInterface
     */
    protected function getResponseViaRelay(array $middlewares, ?ServerRequestInterface $request = null, ?ResponseInterface $response = null): ResponseInterface
    {
        $request = $request ?: ServerRequestFactory::fromGlobals();
        $response = $response ?: new Response();

        $relayBuilder = new RelayBuilder();
        $relay = $relayBuilder->newInstance($middlewares);

        return $relay($request, $response);
    }
}
