<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy;

use Monolog\LogRecord;

/** Decides whether a record has to be excluded.*/
interface ExclusionStrategyInterface
{
    public function excludeRecord(LogRecord $record): bool;
}
