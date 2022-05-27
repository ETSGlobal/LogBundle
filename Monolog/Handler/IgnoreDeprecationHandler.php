<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\Level;
use Monolog\LogRecord;

class IgnoreDeprecationHandler extends AbstractHandler
{
    public function __construct(int|string|Level $level = Level::Debug)
    {
        parent::__construct($level, false);
    }

    public function handle(LogRecord $record): bool
    {
        return $record['channel'] === 'php' &&
            is_string($record['message']) &&
            str_contains($record['message'], 'User Deprecated');
    }
}
