<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 * Decorates a HttpClientInterface and injects the tracing token in the request.
 */
class HttpClientDecorator implements HttpClientInterface
{
    /** @var HttpClientInterface */
    private $httpClient;

    /** @var TokenCollection */
    private $tokenCollection;

    public function __construct(HttpClientInterface $httpClient, TokenCollection $tokenCollection)
    {
        $this->httpClient = $httpClient;
        $this->tokenCollection = $tokenCollection;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $options['headers']['X-Token-Global'] = $this->tokenCollection->getTokenValue('global');

        return $this->httpClient->request($method, $url, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->httpClient->stream($responses, $timeout);
    }

    public function withOptions(array $options): static
    {
        $clone = clone $this;
        $clone->httpClient = $this->httpClient->withOptions($options);

        return $clone;
    }
}
