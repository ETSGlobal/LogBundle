<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Processor;

use ETSGlobal\LogBundle\Tracing\TokenCollection;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/** @internal */
final class TokenCollectionProcessor implements ProcessorInterface
{
    public function __construct(private TokenCollection $tokenCollection)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        /** @var array $extra */
        $extra = $record['extra'];

        foreach ($this->tokenCollection->getTokens() as $token) {
            $extra['token_' . $token->getName()] = $token->getValue();
        }

        $record['extra'] = $extra;

        return $record;
    }
}
