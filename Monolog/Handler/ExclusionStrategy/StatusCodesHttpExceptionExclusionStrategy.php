<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/** @internal */
final class StatusCodesHttpExceptionExclusionStrategy implements ExclusionStrategyInterface
{
    public function __construct(private array $excludedStatusCodes)
    {
    }

    public function excludeRecord(array $record): bool
    {
        if (!isset($record['context']['exception'])) {
            return false;
        }

        $exception = $record['context']['exception'];
        if (!$exception instanceof HttpExceptionInterface) {
            return false;
        }

        return \in_array($exception->getStatusCode(), $this->excludedStatusCodes, true);
    }
}
