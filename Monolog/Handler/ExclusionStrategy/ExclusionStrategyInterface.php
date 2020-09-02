<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy;

/**
 * Decides whether a record has to be excluded.
 */
interface ExclusionStrategyInterface
{
    public function excludeRecord(array $record): bool;
}
