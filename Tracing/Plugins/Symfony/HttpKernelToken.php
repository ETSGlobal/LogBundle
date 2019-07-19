<?php
declare(strict_types=1);

namespace ETSGlobal\LogBundle\Tracing\Plugins\Symfony;

use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpKernelToken
{
    /** @var TokenCollection */
    private $tokenCollection;

    public function __construct(TokenCollection $tokenCollection)
    {
        $this->tokenCollection = $tokenCollection;
    }

    /**
     * Adds the "global" token to the TokenCollection.
     *
     * If the "global" token is not found in the incoming request HTTP headers,
     * it will be initialized, otherwise its value is preserved.
     */
    public function setFromRequest(Request $request): void
    {
        $header = $request->headers->get('x-token-global');
        if (\is_array($header)) {
            $header = implode('', $header);
        }

        $this->tokenCollection->add(
            'global',
            $header ?? null,
            true
        );
    }

    /**
     * Sets all tokens in the response headers.
     */
    public function setToResponse(Response $response): void
    {
        foreach ($this->tokenCollection->getTokens() as $token) {
            $response->headers->set(sprintf('x-token-%s', strtolower($token->getName())), $token->getValue());
        }
    }

    /**
     * Clears the global token.
     */
    public function clear(): void
    {
        $this->tokenCollection->remove('global');
    }
}
