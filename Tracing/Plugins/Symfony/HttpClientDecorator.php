<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Symfony\Component\HttpClient\DecoratorTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/** Decorates a HttpClientInterface and injects the tracing token in the request.*/
class HttpClientDecorator implements HttpClientInterface
{
    use DecoratorTrait;

    public function __construct(private HttpClientInterface $client, private TokenCollection $tokenCollection)
    {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $options['headers']['X-Token-Global'] = $this->tokenCollection->getTokenValue('global');

        return $this->client->request($method, $url, $options);
    }
}
