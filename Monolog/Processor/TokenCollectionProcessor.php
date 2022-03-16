<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Processor;

use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Monolog\Processor\ProcessorInterface;

/** @internal */
final class TokenCollectionProcessor implements ProcessorInterface
{
    public function __construct(private TokenCollection $tokenCollection)
    {
    }

    public function __invoke(array $record): array
    {
        foreach ($this->tokenCollection->getTokens() as $token) {
            $record['extra']['token_' . $token->getName()] = $token->getValue();
        }

        return $record;
    }
}
