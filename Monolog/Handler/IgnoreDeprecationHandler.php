<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class IgnoreDeprecationHandler extends AbstractHandler
{
    public function __construct(int|string $level = Logger::DEBUG)
    {
        parent::__construct($level, false);
    }

    public function handle(array $record): bool
    {
        return $record['channel'] === 'php' && str_contains($record['message'], 'User Deprecated');
    }
}
