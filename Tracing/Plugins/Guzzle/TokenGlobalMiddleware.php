<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Tracing\Plugins\Guzzle;

use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Psr\Http\Message\RequestInterface;

/**
 * Guzzle middleware to forward the "global" token through HTTP calls.
 *
 * @internal
 */
final class TokenGlobalMiddleware
{
    /** @var TokenCollection */
    private $tokenCollection;

    public function __construct(TokenCollection $tokenCollection)
    {
        $this->tokenCollection = $tokenCollection;
    }

    /**
     * Sets the "global" token in the "x-token-global" request header.
     */
    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $tokenGlobal = $this->tokenCollection->getTokenValue('global');

            if ($tokenGlobal !== null) {
                $request = $request->withHeader('x-token-global', $tokenGlobal);
            }

            return $handler($request, $options);
        };
    }
}
